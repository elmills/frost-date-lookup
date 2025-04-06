<?php
/**
 * GitHub Plugin Updater
 *
 * @package   GitHubPluginUpdater
 * @author    Your Name
 * @license   GPL-2.0+
 * @link      https://yourwebsite.com
 *
 * This class handles WordPress plugin updates from GitHub repositories
 * and automatically converts README.md to readme.txt for the "View Details" link.
 * 
 * Requires: https://github.com/YahnisElsts/plugin-update-checker
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('GitHub_Plugin_Updater')) {

    /**
     * GitHub Plugin Updater Class
     */
    class GitHub_Plugin_Updater {

        /**
         * The update checker instance.
         *
         * @var object
         */
        private $update_checker;

        /**
         * Main plugin file path.
         *
         * @var string
         */
        private $plugin_file;

        /**
         * Plugin slug.
         *
         * @var string
         */
        private $plugin_slug;

        /**
         * GitHub repository URL.
         *
         * @var string
         */
        private $repository_url;

        /**
         * GitHub branch name.
         *
         * @var string
         */
        private $branch;

        /**
         * GitHub access token (for private repositories).
         *
         * @var string|null
         */
        private $access_token;

        /**
         * Metadata for the readme.txt file.
         *
         * @var array
         */
        private $metadata;

        /**
         * Initialize the updater.
         *
         * @param string $plugin_file   Main plugin file path (__FILE__).
         * @param string $plugin_slug   Plugin slug (should match directory name).
         * @param string $repository    GitHub repository URL.
         * @param string $branch        GitHub branch name (default: 'main').
         * @param string $access_token  GitHub access token for private repos (optional).
         * @param array  $metadata      Additional metadata for readme.txt (optional).
         */
        public function __construct($plugin_file, $plugin_slug, $repository, $branch = 'main', $access_token = null, $metadata = []) {
            // Check if the update checker library exists
            if (!file_exists(dirname($plugin_file) . '/plugin-update-checker/plugin-update-checker.php')) {
                add_action('admin_notices', [$this, 'missing_library_notice']);
                return;
            }

            require_once dirname($plugin_file) . '/plugin-update-checker/plugin-update-checker.php';

            $this->plugin_file = $plugin_file;
            $this->plugin_slug = $plugin_slug;
            $this->repository_url = $repository;
            $this->branch = $branch;
            $this->access_token = $access_token;
            
            // Default metadata
            $default_metadata = [
                'contributors' => 'your_wp_username',
                'donate_link' => 'https://example.com/donate',
                'tags' => 'plugin, wordpress',
                'requires_php' => '7.2',
                'license' => 'GPLv2 or later',
                'license_uri' => 'https://www.gnu.org/licenses/gpl-2.0.html'
            ];
            
            $this->metadata = wp_parse_args($metadata, $default_metadata);

            // Initialize the updater
            $this->init();
        }

        /**
         * Initialize the update checker and hooks.
         */
        private function init() {
            // Set up the update checker
            $this->setup_update_checker();
            
            // Register activation hook to generate readme.txt
            register_activation_hook($this->plugin_file, [$this, 'convert_readme_to_txt']);
            
            // Register hook to update readme.txt when admin visits plugins page
            add_action('admin_init', [$this, 'maybe_update_readme']);
            
            // Filter plugin info to ensure proper details
            add_filter("puc_request_info_result-{$this->plugin_slug}", [$this, 'ensure_plugin_info'], 10, 2);
        }

        /**
         * Set up the plugin update checker.
         */
        private function setup_update_checker() {
            $this->update_checker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
                $this->repository_url,
                $this->plugin_file,
                $this->plugin_slug
            );

            // Set the branch
            $this->update_checker->setBranch($this->branch);
            
            // Enable release assets (for GitHub releases)
            $this->update_checker->getVcsApi()->enableReleaseAssets();
            
            // Set authentication for private repositories
            if (!empty($this->access_token)) {
                $this->update_checker->setAuthentication($this->access_token);
            }
        }

        /**
         * Display admin notice if the update checker library is missing.
         */
        public function missing_library_notice() {
            ?>
            <div class="notice notice-error">
                <p>
                    <?php printf(
                        esc_html__('The %1$s plugin requires the Plugin Update Checker library. Please download it from %2$s and add it to your plugin directory.', 'github-plugin-updater'),
                        '<strong>' . esc_html(get_plugin_data($this->plugin_file)['Name']) . '</strong>',
                        '<a href="https://github.com/YahnisElsts/plugin-update-checker" target="_blank">GitHub</a>'
                    ); ?>
                </p>
            </div>
            <?php
        }

        /**
         * Convert README.md to readme.txt in WordPress standard format.
         *
         * @return bool True on success, false on failure.
         */
        public function convert_readme_to_txt() {
            $readme_md_path = plugin_dir_path($this->plugin_file) . 'README.md';
            $readme_txt_path = plugin_dir_path($this->plugin_file) . 'readme.txt';
            
            if (!file_exists($readme_md_path)) {
                return false;
            }
            
            $md_content = file_get_contents($readme_md_path);
            $txt_content = '';
            
            // Extract plugin name from H1
            preg_match('/^#\s+(.*?)$/m', $md_content, $plugin_name_matches);
            $plugin_name = isset($plugin_name_matches[1]) ? $plugin_name_matches[1] : get_plugin_data($this->plugin_file)['Name'];
            $txt_content .= "=== $plugin_name ===\n";
            
            // Add metadata
            $txt_content .= "Contributors: {$this->metadata['contributors']}\n";
            
            // Add donate link only if provided
            if (isset($this->metadata['donate_link']) && !empty($this->metadata['donate_link'])) {
                $txt_content .= "Donate link: {$this->metadata['donate_link']}\n";
            }
            
            // Get author information from plugin header
            $plugin_data = get_plugin_data($this->plugin_file);
            $author_name = !empty($plugin_data['Author']) ? $plugin_data['Author'] : 'Everette Mills';
            $author_url = !empty($plugin_data['AuthorURI']) ? $plugin_data['AuthorURI'] : 'https://blueboatsolutions.com';
            
            $txt_content .= "Author: $author_name\n";
            $txt_content .= "Author URI: $author_url\n";
            
            $txt_content .= "Tags: {$this->metadata['tags']}\n";
            
            // Extract requires at least
            preg_match('/^## Requires at least\s*\n(.*?)$/m', $md_content, $requires_matches);
            $requires = isset($requires_matches[1]) ? trim($requires_matches[1]) : '5.0';
            $txt_content .= "Requires at least: $requires\n";
            
            // Extract tested up to
            preg_match('/^## Tested up to\s*\n(.*?)$/m', $md_content, $tested_matches);
            $tested = isset($tested_matches[1]) ? trim($tested_matches[1]) : '';
            if (empty($tested)) {
                $tested = get_bloginfo('version');
            }
            $txt_content .= "Tested up to: $tested\n";
            
            // Extract version from plugin header or from the last changelog entry
            $plugin_data = get_plugin_data($this->plugin_file);
            $version = $plugin_data['Version'];
            
            // Fallback: Try to extract version from changelog
            if (empty($version)) {
                preg_match('/^### ([\d\.]+)/m', $md_content, $version_matches);
                $version = isset($version_matches[1]) ? $version_matches[1] : '1.0.0';
            }
            
            $txt_content .= "Stable tag: $version\n";
            // Extract requires php
            preg_match('/^## Requires PHP\s*\n(.*?)$/m', $md_content, $requires_php_matches);
            $requires_php = isset($requires_php_matches[1]) ? trim($requires_php_matches[1]) : 
                            (isset($this->metadata['requires_php']) ? $this->metadata['requires_php'] : '5.6');
            $txt_content .= "Requires PHP: $requires_php\n";
            $txt_content .= "License: {$this->metadata['license']}\n";
            $txt_content .= "License URI: {$this->metadata['license_uri']}\n\n";
            
            // Extract short description
            preg_match('/^#.*?\n(.*?)(?=^##|\z)/ms', $md_content, $short_desc_matches);
            $short_desc = isset($short_desc_matches[1]) ? trim($short_desc_matches[1]) : '';
            $txt_content .= $short_desc . "\n\n";
            
            // Extract description
            preg_match('/^## Description\s*\n(.*?)(?=^##|\z)/ms', $md_content, $desc_matches);
            $description = isset($desc_matches[1]) ? trim($desc_matches[1]) : '';
            $txt_content .= "== Description ==\n\n" . $description . "\n\n";
            
            // Extract installation
            preg_match('/^## Installation\s*\n(.*?)(?=^##|\z)/ms', $md_content, $install_matches);
            $installation = isset($install_matches[1]) ? trim($install_matches[1]) : '';
            $txt_content .= "== Installation ==\n\n" . $installation . "\n\n";
            
            // Extract usage as FAQ
            preg_match('/^## Usage\s*\n(.*?)(?=^##|\z)/ms', $md_content, $usage_matches);
            $usage = isset($usage_matches[1]) ? trim($usage_matches[1]) : '';
            if (!empty($usage)) {
                $txt_content .= "== Frequently Asked Questions ==\n\n";
                $txt_content .= "= How do I use this plugin? =\n\n" . $usage . "\n\n";
            }
            
            // Extract changelog
            preg_match('/^## Changelog\s*\n(.*?)(?=^##|\z)/ms', $md_content, $changelog_matches);
            $changelog = isset($changelog_matches[1]) ? trim($changelog_matches[1]) : '';
            $txt_content .= "== Changelog ==\n\n";
            
            // Convert markdown changelog format to WordPress format
            $changelog = preg_replace('/^### (.*?)$/m', "= $1 =", $changelog);
            $changelog = preg_replace('/^\* (.*?)$/m', "* $1", $changelog);
            
            $txt_content .= $changelog;
            
            // Write to readme.txt
            return (bool) file_put_contents($readme_txt_path, $txt_content);
        }

        /**
         * Check if we need to update the readme.txt file.
         * 
         * Runs when admin visits plugins page.
         */
        public function maybe_update_readme() {
            if (is_admin() && (
                (isset($_GET['page']) && $_GET['page'] == 'plugins') || 
                (isset($_SERVER['SCRIPT_NAME']) && strpos($_SERVER['SCRIPT_NAME'], 'plugins.php') !== false)
            )) {
                $this->convert_readme_to_txt();
            }
        }

        /**
         * Ensure plugin info is properly set in the "View Details" dialog.
         *
         * @param object $info     Plugin info object.
         * @param mixed  $response Response from the update API.
         * @return object Modified plugin info object.
         */
        public function ensure_plugin_info($info, $response) {
            // Ensure readme.txt is up to date
            $this->convert_readme_to_txt();
            
            return $info;
        }

        /**
         * Get the update checker instance.
         *
         * @return object Update checker instance.
         */
        public function get_update_checker() {
            return $this->update_checker;
        }
    }
}