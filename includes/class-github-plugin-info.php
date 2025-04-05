<?php
/**
 * GitHub Plugin Info
 *
 * This class handles the GitHub integration with plugin-update-checker
 * to use README.md for plugin information.
 *
 * @package    Frost_Date_Lookup
 * @subpackage Frost_Date_Lookup/includes
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Make sure the plugin-update-checker library is loaded
if (!class_exists('YahnisElsts\PluginUpdateChecker\v5\PucFactory')) {
    require_once plugin_dir_path(dirname(__FILE__)) . 'vendor/plugin-update-checker/plugin-update-checker.php';
}

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

class GitHub_Plugin_Info {
    /**
     * The update checker instance.
     *
     * @var \YahnisElsts\PluginUpdateChecker\v5\PucFactory
     */
    private $update_checker;

    /**
     * Initialize the class and set up the update checker.
     *
     * @param string $github_url   The GitHub repository URL.
     * @param string $main_file    The main plugin file path.
     * @param string $plugin_slug  The plugin slug.
     */
    public function __construct($github_url, $main_file, $plugin_slug) {
        // Set up the update checker
        $this->update_checker = PucFactory::buildUpdateChecker(
            $github_url,
            $main_file,
            $plugin_slug
        );

        // Configure to use GitHub releases for updates
        $this->update_checker->getVcsApi()->enableReleaseAssets();

        // Add filters
        $this->add_filters();
    }

    /**
     * Add the necessary filters for the update checker.
     */
    private function add_filters() {
        // Force using README.md instead of readme.txt
        $this->update_checker->addFilter('readme_name', function() {
            return 'README.md';
        });

        // Custom parser for README.md
        $this->update_checker->addFilter('parse_readme', array($this, 'parse_readme_md'), 10, 2);
    }

    /**
     * Parse the README.md file for plugin information.
     *
     * @param array  $readme    The readme data array.
     * @param string $fileName  The file path.
     * @return array            The parsed readme data.
     */
    public function parse_readme_md($readme, $fileName) {
        // Only apply custom parsing to README.md
        if (basename($fileName) !== 'README.md') {
            return $readme;
        }
        
        // Parse the markdown content
        $content = file_get_contents($fileName);
        $data = array();
        
        // Extract version
        if (preg_match('/Version:\s*([^\s\n]+)/', $content, $matches)) {
            $data['version'] = trim($matches[1]);
        }
        
        // Extract requires
        if (preg_match('/Requires at least:\s*([^\s\n]+)/', $content, $matches)) {
            $data['requires'] = trim($matches[1]);
        }
        
        // Extract tested up to
        if (preg_match('/Tested up to:\s*([^\s\n]+)/', $content, $matches)) {
            $data['tested'] = trim($matches[1]);
        }
        
        // Extract author
        if (preg_match('/Author:\s*(.+)/', $content, $matches)) {
            $data['author'] = trim($matches[1]);
        }
        
        // Extract author URI
        if (preg_match('/Author URI:\s*(.+)/', $content, $matches)) {
            $data['author_homepage'] = trim($matches[1]);
        }
        
        // Extract sections
        $sections = array();
        
        // Description section
        if (preg_match('/## Description\s*(.+?)(?=##|\z)/s', $content, $matches)) {
            $sections['description'] = trim($matches[1]);
        }
        
        // Installation section
        if (preg_match('/## Installation\s*(.+?)(?=##|\z)/s', $content, $matches)) {
            $sections['installation'] = trim($matches[1]);
        }
        
        // Changelog section
        if (preg_match('/## Changelog\s*(.+?)(?=##|\z)/s', $content, $matches)) {
            $sections['changelog'] = trim($matches[1]);
        }
        
        $data['sections'] = $sections;
        
        return $data;
    }

    /**
     * Get plugin changelog from README.md
     *
     * @param string $file Full path to the README.md file
     * @return string HTML formatted changelog or error message
     */
    public function get_changelog($file = null) {
        if (null === $file) {
            $file = plugin_dir_path(dirname(__FILE__)) . 'README.md';
        }

        if (!file_exists($file)) {
            return '<p class="notice notice-error">README.md file not found.</p>';
        }

        $readme_content = file_get_contents($file);
        
        // Look for the changelog section using proper regex
        if (preg_match('/(?:^|\n)[#]{1,2}\s*Changelog\s*(?:\n|\r\n)(.*?)(?:\n[#]{1,2}|$)/s', $readme_content, $matches)) {
            $changelog = trim($matches[1]);
            
            // Check if we actually have content
            if (empty($changelog)) {
                return '<p class="notice notice-warning">Changelog section found but appears to be empty.</p>';
            }
            
            // Parse markdown to HTML if we have the capability
            if (class_exists('Parsedown')) {
                return Parsedown::instance()->text($changelog);
            } else {
                // Simple formatting fallback if Parsedown isn't available
                $formatted = nl2br(esc_html($changelog));
                $formatted = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $formatted);
                $formatted = preg_replace('/\*(.*?)\*/s', '<em>$1</em>', $formatted);
                return $formatted;
            }
        }
        
        return '<p class="notice notice-warning">No changelog section found in README.md file.</p>';
    }

    /**
     * Display the changelog in the plugin info tab
     *
     * @param array $plugin_data Plugin data
     * @param string $status Plugin status
     * @param int $active_installs Number of active installs
     * @param array $locales Locales
     * @return void
     */
    public function display_changelog($plugin_data, $status, $active_installs, $locales) {
        echo '<div class="changelog-content">';
        echo wp_kses_post($this->get_changelog());
        echo '</div>';
    }
}