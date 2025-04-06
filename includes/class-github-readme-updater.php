<?php
/**
 * GitHub Readme Updater.
 *
 * Handles GitHub integration and readme.txt generation for WordPress plugins.
 *
 * @link       https://github.com/elmills/
 * @since      1.0.27
 *
 * @package    WordPress
 * @subpackage GitHub_Integration
 */

/**
 * GitHub Readme Updater Class.
 *
 * This class handles GitHub integration and readme.txt generation for WordPress plugins.
 * It provides functionality for:
 * - Generating readme.txt from README.md
 * - Setting up GitHub-based plugin updates
 * - Checking for update requirements
 * - Handling various WordPress hooks related to plugin updates
 *
 * @since      1.0.0
 * @package    WordPress
 * @subpackage GitHub_Integration
 * @author     Frost Date Plugin <admin@emb.wedploy.dev>
 */
class GitHub_Readme_Updater {
    /**
     * The plugin file path.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_file    The plugin file path.
     */
    private $plugin_file;

    /**
     * The plugin slug.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_slug    The plugin slug.
     */
    private $plugin_slug;

    /**
     * The GitHub repository URL.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $github_repo_url    The GitHub repository URL.
     */
    private $github_repo_url;

    /**
     * The GitHub branch.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $github_branch    The GitHub branch.
     */
    private $github_branch;

    /**
     * The GitHub token.
     *
     * @since    1.0.0
     * @access   private
     * @var      string|null    $github_token    The GitHub token.
     */
    private $github_token;

    /**
     * The plugin metadata.
     *
     * @since    1.0.0
     * @access   private
     * @var      array    $plugin_metadata    The plugin metadata.
     */
    private $plugin_metadata;

    /**
     * The updater class path.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $updater_class_path    The updater class path.
     */
    private $updater_class_path;

    /**
     * The library path.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $library_path    The library path.
     */
    private $library_path;

    /**
     * The text domain.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $text_domain    The text domain.
     */
    private $text_domain;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string    $plugin_file           The plugin file path.
     * @param    string    $plugin_slug           The plugin slug.
     * @param    string    $github_repo_url       The GitHub repository URL.
     * @param    string    $github_branch         The GitHub branch.
     * @param    string|null    $github_token     The GitHub token.
     * @param    array     $plugin_metadata       The plugin metadata.
     * @param    string    $updater_class_path    The updater class path.
     * @param    string    $library_path          The library path.
     * @param    string    $text_domain           The text domain.
     */
    public function __construct(
        $plugin_file,
        $plugin_slug,
        $github_repo_url,
        $github_branch,
        $github_token = null,
        $plugin_metadata = array(),
        $updater_class_path = 'includes/class-github-plugin-info.php',
        $library_path = 'plugin-update-checker/plugin-update-checker.php',
        $text_domain = 'github-updater'
    ) {
        $this->plugin_file = $plugin_file;
        $this->plugin_slug = $plugin_slug;
        $this->github_repo_url = $github_repo_url;
        $this->github_branch = $github_branch;
        $this->github_token = $github_token;
        $this->plugin_metadata = $plugin_metadata;
        $this->updater_class_path = $updater_class_path;
        $this->library_path = $library_path;
        $this->text_domain = $text_domain;

        // Register all hooks
        $this->register_hooks();
    }

    /**
     * Register all hooks related to plugin updates.
     *
     * @since    1.0.0
     * @access   private
     */
    private function register_hooks() {
        // Initialize the updater with a lower priority to ensure all dependencies are loaded
        add_action('plugins_loaded', array($this, 'initialize_github_updater'), 20);

        // Register activation hook
        register_activation_hook($this->plugin_file, array($this, 'plugin_activation_updater_checks'));

        // Show admin notices after activation
        add_action('admin_notices', array($this, 'plugin_activation_updater_notices'));

        // Generate readme.txt on plugin activation
        add_action('activated_plugin', array($this, 'on_activated_plugin'));

        // Run during plugin information API requests
        add_action('plugins_api', array($this, 'on_plugins_api'), 1, 3);

        // Run when viewing plugins list
        add_action('after_plugin_row', array($this, 'on_after_plugin_row'), 10, 2);

        // Run during WP update check process
        add_action('update_option_update_plugins', array($this, 'on_update_option_update_plugins'));
    }

    /**
     * Helper function to display admin notices consistently throughout the plugin.
     *
     * @since    1.0.0
     * @access   public
     * @param    string    $type           Notice type (error, warning, success, info).
     * @param    string    $message        The message to display.
     * @param    bool      $dismissible    Whether the notice should be dismissible.
     */
    public function display_admin_notice($type, $message, $dismissible = true) {
        $dismissible_class = $dismissible ? ' is-dismissible' : '';
        ?>
        <div class="notice notice-<?php echo esc_attr($type) . $dismissible_class; ?>">
            <p><?php echo $message; ?></p>
        </div>
        <?php
    }

    /**
     * Initialize the GitHub Updater.
     *
     * @since    1.0.0
     * @access   public
     */
    public function initialize_github_updater() {
        if (!function_exists('get_plugin_data')) {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }

        // Check if the GitHub Plugin Updater class exists
        $updater_class_full_path = plugin_dir_path($this->plugin_file) . $this->updater_class_path;
        if (!file_exists($updater_class_full_path)) {
            // Display admin error notice
            add_action('admin_notices', function() use ($updater_class_full_path) {
                $this->display_admin_notice(
                    'error',
                    sprintf(
                        esc_html__('GitHub Plugin Updater class file is missing! Plugin updates will not work correctly. Please add the file to: %s', $this->text_domain),
                        esc_html($updater_class_full_path)
                    )
                );
            });
            return;
        }

        // Include the GitHub Plugin Updater class
        require_once $updater_class_full_path;

        // Check if the Plugin Update Checker library exists
        $library_full_path = plugin_dir_path($this->plugin_file) . $this->library_path;
        if (!file_exists($library_full_path)) {
            // Display admin error notice
            add_action('admin_notices', function() use ($library_full_path) {
                $this->display_admin_notice(
                    'error',
                    esc_html__('Plugin Update Checker library is missing! Please download it from', $this->text_domain) . 
                    ' <a href="https://github.com/YahnisElsts/plugin-update-checker" target="_blank">GitHub</a> ' .
                    sprintf(
                        esc_html__('and add it to: %s', $this->text_domain),
                        esc_html($library_full_path)
                    )
                );
            });
            return;
        }

        // Check for class existence
        if (!class_exists('GitHub_Plugin_Updater')) {
            // Display admin error notice
            add_action('admin_notices', function() {
                $this->display_admin_notice(
                    'error',
                    esc_html__('GitHub Plugin Updater class not found! Check your implementation.', $this->text_domain)
                );
            });
            return;
        }

        // Check if README.md exists
        $readme_path = plugin_dir_path($this->plugin_file) . 'README.md';
        if (!file_exists($readme_path)) {
            // Display admin warning notice
            add_action('admin_notices', function() {
                $this->display_admin_notice(
                    'warning',
                    esc_html__('README.md file is missing! Plugin update details may not display correctly.', $this->text_domain)
                );
            });
        }

        try {
            // Get plugin data for metadata
            $plugin_data = get_plugin_data($this->plugin_file);

            // Merge plugin data with custom metadata
            $combined_metadata = array_merge($this->plugin_metadata, [
                'plugin_name' => $plugin_data['Name'],
                'plugin_version' => $plugin_data['Version'],
                'plugin_author' => $plugin_data['Author'],
                'plugin_description' => $plugin_data['Description']
            ]);

            // Create a new updater instance with explicit parameters
            $updater = new GitHub_Plugin_Updater(
                $this->plugin_file,
                $this->plugin_slug,
                $this->github_repo_url,
                $this->github_branch,
                $this->github_token,
                $combined_metadata
            );

            // Clear update caches
            $this->clear_plugin_update_caches();

            // Generate readme.txt using the new converter
            $this->force_readme_txt_generation(true);

        } catch (Exception $e) {
            // Log the error and add admin notice
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('GitHub Plugin Updater Error: ' . $e->getMessage());
            }

            // Display admin error notice
            add_action('admin_notices', function() use ($e) {
                $this->display_admin_notice(
                    'error',
                    esc_html__('Error initializing GitHub Plugin Updater:', $this->text_domain) . ' ' . esc_html($e->getMessage())
                );
            });
        }
    }

    /**
     * Check for plugin update requirements on activation.
     *
     * @since    1.0.0
     * @access   public
     */
    public function plugin_activation_updater_checks() {
        // Check for critical dependencies
        $library_full_path = plugin_dir_path($this->plugin_file) . $this->library_path;
        if (!file_exists($library_full_path)) {
            // Deactivate the plugin and show an error
            deactivate_plugins(plugin_basename($this->plugin_file));
            wp_die(
                esc_html__('This plugin requires the Plugin Update Checker library. Please install it before activating.', $this->text_domain),
                esc_html__('Plugin Activation Error', $this->text_domain),
                ['back_link' => true]
            );
        }

        // Check for README.md file
        $readme_path = plugin_dir_path($this->plugin_file) . 'README.md';
        if (!file_exists($readme_path)) {
            // Just show a warning but allow activation
            set_transient('plugin_missing_readme', true, 5);
        }
    }

    /**
     * Show admin notice for missing README.md after activation.
     *
     * @since    1.0.0
     * @access   public
     */
    public function plugin_activation_updater_notices() {
        // Check for the transient
        if (get_transient('plugin_missing_readme')) {
            $this->display_admin_notice(
                'warning',
                esc_html__('README.md file is missing! Plugin update details may not display correctly.', $this->text_domain),
                true
            );

            // Delete the transient
            delete_transient('plugin_missing_readme');
        }
    }

    /**
     * Clear plugin update caches to ensure fresh update checks.
     *
     * @since    1.0.0
     * @access   public
     */
    public function clear_plugin_update_caches() {
        // Clear update-related caches
        delete_site_transient('update_plugins');
        delete_site_transient('puc_check_count_' . $this->plugin_slug);
        delete_site_transient('puc_request_info_' . $this->plugin_slug);

        // Force WordPress to check for plugin updates
        wp_update_plugins();
    }

    /**
     * Handler for the activated_plugin hook.
     *
     * @since    1.0.0
     * @access   public
     * @param    string    $plugin    The plugin that was activated.
     */
    public function on_activated_plugin($plugin) {
        if (plugin_basename($this->plugin_file) === $plugin) {
            $this->force_readme_txt_generation(true);
        }
    }

    /**
     * Handler for the plugins_api hook.
     *
     * @since    1.0.0
     * @access   public
     * @param    mixed     $result    The result of the API call.
     * @param    string    $action    The API action being performed.
     * @param    object    $args      The arguments for the API call.
     * @return   mixed                The result of the API call.
     */
    public function on_plugins_api($result, $action, $args) {
        // Only run for our plugin
        if ($action === 'plugin_information' && isset($args->slug) && $args->slug === $this->plugin_slug) {
            $this->force_readme_txt_generation(true); // Force regeneration during plugin info requests
        }
        return $result;
    }

    /**
     * Handler for the after_plugin_row hook.
     *
     * @since    1.0.0
     * @access   public
     * @param    string    $plugin_file    The plugin file.
     * @param    array     $plugin_data    The plugin data.
     */
    public function on_after_plugin_row($plugin_file, $plugin_data) {
        if (plugin_basename($this->plugin_file) === $plugin_file) {
            $this->force_readme_txt_generation(false);
        }
    }

    /**
     * Handler for the update_option_update_plugins hook.
     *
     * @since    1.0.0
     * @access   public
     */
    public function on_update_option_update_plugins() {
        $this->force_readme_txt_generation(true);
    }

    /**
     * Generate readme.txt for plugin updates using GitHub_To_WordPress_Readme_Converter.
     *
     * This function utilizes GitHub_To_WordPress_Readme_Converter for readme.txt generation
     * with performance optimizations to prevent excessive regeneration.
     *
     * @since    1.0.0
     * @access   public
     * @param    bool    $force    Whether to force regeneration.
     * @return   bool              True if file was generated, false otherwise.
     */
    public function force_readme_txt_generation($force = false) {
        // Check if we've recently generated the file (within last hour)
        // Skip this check if force = true
        if (!$force && get_transient('readme_txt_generated')) {
            return false;
        }
        
        $readme_md_path = plugin_dir_path($this->plugin_file) . 'README.md';
        $readme_txt_path = plugin_dir_path($this->plugin_file) . 'readme.txt';
        
        // Only regenerate if:
        // 1. README.md exists, AND
        // 2. readme.txt doesn't exist OR force parameter is true
        if (file_exists($readme_md_path) && ($force || !file_exists($readme_txt_path))) {
            try {
                // Initialize the converter
                $converter = new GitHub_To_WordPress_Readme_Converter($readme_md_path);
                
                // Get recommendations for improving the README.md file (for debug/admin purposes)
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    $recommendations = $converter->get_recommendations();
                    if (!empty($recommendations)) {
                        foreach ($recommendations as $recommendation) {
                            error_log('README.md Recommendation: ' . $recommendation);
                        }
                    }
                }
                
                // Convert and save the readme.txt file
                if ($converter->save_readme_txt($readme_txt_path)) {
                    // Set transient to prevent frequent regeneration
                    set_transient('readme_txt_generated', true, HOUR_IN_SECONDS);
                    
                    // Only clear update cache if we actually generated a new file
                    if (file_exists($readme_txt_path)) {
                        $this->clear_plugin_update_caches();
                    }
                    
                    return true;
                }
            } catch (Exception $e) {
                // Log the error
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('README.md to readme.txt conversion error: ' . $e->getMessage());
                }
            }
        }
        
        return false;
    }
}
?>