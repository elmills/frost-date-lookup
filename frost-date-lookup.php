<?php
/**
 * Plugin Name: Frost Date Lookup
 * Plugin URI: https://github.com/elmills/frost-date-lookup
 * Description: A plugin to retrieve average frost-free dates based on zip code using NOAA/NWS data.
 * Version: 1.0.20
 * Author: Everette Mills
 * Author URI: https://blueboatsolutions.com
 * License: GPL2
 * Requires at least: 6.0
 * Requires PHP: 8.1
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('FROST_DATE_LOOKUP_PATH', plugin_dir_path(__FILE__));
define('FROST_DATE_LOOKUP_URL', plugin_dir_url(__FILE__));

// Include necessary files
require_once plugin_dir_path( __FILE__ ) . 'includes/class-frost-date-lookup.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-frost-date-loader.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-frost-date-activator.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-frost-date-deactivator.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-frost-date-i18n.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-frost-date.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-frost-date-api.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-github-plugin-info.php';


// Activation and deactivation hooks
register_activation_hook( __FILE__, array( 'Frost_Date_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Frost_Date_Deactivator', 'deactivate' ) );

// Initialize the plugin
function frost_date_lookup_init() {
    $plugin = new Frost_Date_Lookup();
    $plugin->init();
}
add_action('plugins_loaded', 'frost_date_lookup_init');

function run_frost_date_lookup() {
    $plugin = new Frost_Date_Loader();
    $plugin->run();
}
run_frost_date_lookup();

// Enqueue scripts and styles
function frost_date_lookup_enqueue_scripts() {
    wp_enqueue_style('frost-date-lookup-style', FROST_DATE_LOOKUP_URL . 'assets/css/frost-date-lookup.css', array(), '1.0.20');
    wp_enqueue_script('frost-date-lookup-script', FROST_DATE_LOOKUP_URL . 'assets/js/frost-date-lookup.js', array('jquery'), '1.0.20', true);
    
    // Localize the script with new data
    wp_localize_script('frost-date-lookup-script', 'frost_date_lookup', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('frost_date_lookup_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'frost_date_lookup_enqueue_scripts');

// Register shortcode and callback
add_shortcode('frost_date_lookup', 'frost_date_lookup_shortcode');

function frost_date_lookup_shortcode($atts) {
    // Start output buffering to capture the output
    ob_start();
    
    // Include the form template
    include FROST_DATE_LOOKUP_PATH . 'templates/lookup-form.php';
    
    // Get the buffered content and return it
    $output = ob_get_clean();
    return $output;
}

/**
 * Plugin Update Checker
 */

/**
 * Generic implementation of the GitHub Plugin Updater
 * 
 * Include this code in your main plugin file and customize the variables at the top
 */

// ====================================================================
// CONFIGURATION - Edit these variables to match your plugin
// ====================================================================

// The slug used for your plugin (should match directory name)
$plugin_slug = 'frost-date-lookup';

// Your GitHub repository URL
$github_repo_url = 'https://github.com/elmills/frost-date-lookup';

// GitHub branch that contains the stable release (usually 'main' or 'master')
$github_branch = 'main';

// GitHub access token for private repositories (null for public repos)
$github_token = null;

// Plugin metadata for readme.txt 
$plugin_metadata = [
    'contributors' => 'your_wp_username',
    // 'donate_link' => 'https://example.com/donate', // Optional - uncomment if needed
    'tags' => 'plugin, wordpress',
    // 'requires_php' => '7.2' // Optional - will be pulled from README.md if present
];

// Paths to required files (change if your structure is different)
$updater_class_path = 'includes/class-github-plugin-info.php';
$library_path = 'plugin-update-checker/plugin-update-checker.php';

// Text domain for translations
$text_domain = 'plugin-updater';

// ====================================================================
// IMPLEMENTATION - No need to edit below this line
// ====================================================================

/**
 * Initialize the GitHub Updater
 * 
 * @return void
 */
function initialize_github_updater() {
    // Make configuration variables available
    global $plugin_slug, $github_repo_url, $github_branch, $github_token, 
           $plugin_metadata, $updater_class_path, $library_path, $text_domain;
    
    // Define plugin file and directory constants if not already defined
    if (!defined('PLUGIN_FILE')) {
        define('PLUGIN_FILE', __FILE__);
    }
    
    if (!defined('PLUGIN_DIR')) {
        define('PLUGIN_DIR', plugin_dir_path(PLUGIN_FILE));
    }
    
    // Check if we're in WP context
    if (!function_exists('get_plugin_data')) {
        return;
    }
    
    // Step 1: Check if the GitHub Plugin Updater class exists
    $updater_class_full_path = PLUGIN_DIR . $updater_class_path;
    if (!file_exists($updater_class_full_path)) {
        // Add admin notice if the class file is missing
        add_action('admin_notices', function() use ($text_domain) {
            ?>
            <div class="notice notice-error">
                <p><?php printf(
                    esc_html__('GitHub Plugin Updater class file is missing! Plugin updates will not work correctly. Please add the file to: %s', $text_domain),
                    esc_html(PLUGIN_DIR . $GLOBALS['updater_class_path'])
                ); ?></p>
            </div>
            <?php
        });
        return;
    }
    
    // Include the GitHub Plugin Updater class
    require_once $updater_class_full_path;
    
    // Step 2: Check if the Plugin Update Checker library exists
    $library_full_path = PLUGIN_DIR . $library_path;
    if (!file_exists($library_full_path)) {
        // Add admin notice if the library is missing
        add_action('admin_notices', function() use ($text_domain) {
            ?>
            <div class="notice notice-error">
                <p>
                    <?php esc_html_e('Plugin Update Checker library is missing! Please download it from', $text_domain); ?>
                    <a href="https://github.com/YahnisElsts/plugin-update-checker" target="_blank">GitHub</a>
                    <?php printf(
                        esc_html__('and add it to: %s', $text_domain),
                        esc_html(PLUGIN_DIR . $GLOBALS['library_path'])
                    ); ?>
                </p>
            </div>
            <?php
        });
        return;
    }
    
    // Step 3: Check for class existence to avoid duplicate initialization
    if (!class_exists('GitHub_Plugin_Updater')) {
        // Add admin notice if the class doesn't exist after including the file
        add_action('admin_notices', function() use ($text_domain) {
            ?>
            <div class="notice notice-error">
                <p><?php esc_html_e('GitHub Plugin Updater class not found! Check your implementation.', $text_domain); ?></p>
            </div>
            <?php
        });
        return;
    }
    
    // Step 4: Check if README.md exists
    $readme_path = PLUGIN_DIR . 'README.md';
    if (!file_exists($readme_path)) {
        // Add admin notice if README.md is missing
        add_action('admin_notices', function() use ($text_domain) {
            ?>
            <div class="notice notice-warning">
                <p><?php esc_html_e('README.md file is missing! Plugin update details may not display correctly.', $text_domain); ?></p>
            </div>
            <?php
        });
        // Continue anyway as the updater will still work
    }
    
    // Step 5: Initialize the updater with appropriate error handling
    try {
        // Get plugin data for metadata
        $plugin_data = get_plugin_data(PLUGIN_FILE);
        
        // Merge plugin data with custom metadata
        $combined_metadata = array_merge($plugin_metadata, [
            'plugin_name' => $plugin_data['Name'],
            'plugin_version' => $plugin_data['Version'],
            'plugin_author' => $plugin_data['Author'],
            'plugin_description' => $plugin_data['Description']
        ]);
        
        // Create a new updater instance
        new GitHub_Plugin_Updater(
            PLUGIN_FILE,             // Main plugin file
            $plugin_slug,            // Plugin slug
            $github_repo_url,        // GitHub repository URL
            $github_branch,          // GitHub branch
            $github_token,           // GitHub access token
            $combined_metadata       // Combined metadata
        );
    } catch (Exception $e) {
        // Log the error and add admin notice
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('GitHub Plugin Updater Error: ' . $e->getMessage());
        }
        
        add_action('admin_notices', function() use ($e, $text_domain) {
            ?>
            <div class="notice notice-error">
                <p><?php esc_html_e('Error initializing GitHub Plugin Updater:', $text_domain); ?> <?php echo esc_html($e->getMessage()); ?></p>
            </div>
            <?php
        });
    }
}

// Hook the initialization after all plugins are loaded
add_action('plugins_loaded', 'initialize_github_updater');

/**
 * Check for plugin update requirements on activation
 */
function plugin_activation_updater_checks() {
    global $library_path, $text_domain;
    
    // Check for critical dependencies
    $library_full_path = plugin_dir_path(__FILE__) . $library_path;
    if (!file_exists($library_full_path)) {
        // Deactivate the plugin and show an error
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(
            esc_html__('This plugin requires the Plugin Update Checker library. Please install it before activating.', $text_domain),
            esc_html__('Plugin Activation Error', $text_domain),
            ['back_link' => true]
        );
    }
    
    // Check for README.md file
    $readme_path = plugin_dir_path(__FILE__) . 'README.md';
    if (!file_exists($readme_path)) {
        // Just show a warning but allow activation
        set_transient('plugin_missing_readme', true, 5);
    }
}
register_activation_hook(__FILE__, 'plugin_activation_updater_checks');

/**
 * Show admin notice for missing README.md after activation
 */
function plugin_activation_updater_notices() {
    global $text_domain;
    
    // Check for the transient
    if (get_transient('plugin_missing_readme')) {
        ?>
        <div class="notice notice-warning is-dismissible">
            <p><?php esc_html_e('README.md file is missing! Plugin update details may not display correctly.', $text_domain); ?></p>
        </div>
        <?php
        // Delete the transient
        delete_transient('plugin_missing_readme');
    }
}
add_action('admin_notices', 'plugin_activation_updater_notices');

/**
 * Force WordPress to check for plugin updates and display debug info
 */
function force_plugin_update_check() {
    // Only run on admin pages
    if (!is_admin()) {
        return;
    }
    
    // Clear update cache
    delete_site_transient('update_plugins');
    delete_site_transient('puc_check_count_' . $GLOBALS['plugin_slug']);
    delete_site_transient('puc_request_info_' . $GLOBALS['plugin_slug']);
    
    // Force WordPress to check for plugin updates
    wp_update_plugins();
    
    // Add admin notice with diagnostic info
    add_action('admin_notices', 'display_update_debug_info');
}
add_action('admin_init', 'force_plugin_update_check');

/**
 * Display diagnostic information about the update process
 */
function display_update_debug_info() {
    global $plugin_slug, $github_repo_url, $github_branch;
    
    // Get plugin data
    $plugin_data = get_plugin_data(__FILE__);
    $local_version = $plugin_data['Version'];
    
    // Try to get update information
    $update_data = get_site_transient('update_plugins');
    $has_update = isset($update_data->response[$plugin_slug . '/frost-date-lookup.php']);
    
    // Get information about available version
    $available_version = $has_update ? $update_data->response[$plugin_slug . '/frost-date-lookup.php']->new_version : 'Unknown';
    
    // Check readme.txt status
    $readme_txt_path = plugin_dir_path(__FILE__) . 'readme.txt';
    $readme_md_path = plugin_dir_path(__FILE__) . 'README.md';
    $readme_txt_exists = file_exists($readme_txt_path);
    
    // Force readme.txt generation
    if (file_exists($readme_md_path)) {
        // Create an instance to convert README.md to readme.txt
        require_once plugin_dir_path(__FILE__) . 'includes/class-github-plugin-info.php';
        $updater = new GitHub_Plugin_Updater(
            __FILE__,
            $plugin_slug,
            $github_repo_url,
            $github_branch,
            null,
            []
        );
        
        // Manually trigger the conversion
        $updater->convert_readme_to_txt();
        
        // Refresh status
        $readme_txt_exists = file_exists($readme_txt_path);
    }
    
    // Get readme.txt content for debugging
    $readme_txt_content = '';
    if ($readme_txt_exists) {
        $readme_txt_content = file_get_contents($readme_txt_path);
    }
    
    // Get the plugin info directly
    $plugin_info = get_plugin_data(__FILE__, false, false);
    
    // Display diagnostic information
    ?>
    <div class="notice notice-info is-dismissible">
        <h3>Frost Date Lookup Update Diagnostics</h3>
        <p><strong>Local Version:</strong> <?php echo esc_html($local_version); ?></p>
        <p><strong>Available Version:</strong> <?php echo esc_html($available_version); ?></p>
        <p><strong>Repository URL:</strong> <?php echo esc_html($github_repo_url); ?></p>
        <p><strong>Branch:</strong> <?php echo esc_html($github_branch); ?></p>
        <p><strong>Update Available:</strong> <?php echo $has_update ? 'Yes' : 'No'; ?></p>
        <p><strong>Last Check:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
        
        <h4>View Details Diagnostics</h4>
        <p><strong>readme.txt Exists:</strong> <?php echo $readme_txt_exists ? 'Yes' : 'No'; ?></p>
        <p><strong>README.md Exists:</strong> <?php echo file_exists($readme_md_path) ? 'Yes' : 'No'; ?></p>
        <p><strong>Plugin Name:</strong> <?php echo esc_html($plugin_info['Name']); ?></p>
        <p><strong>Plugin URI:</strong> <?php echo esc_html($plugin_info['PluginURI']); ?></p>
        
        <?php if ($readme_txt_exists): ?>
        <div style="max-height: 150px; overflow-y: auto; background: #f5f5f5; padding: 10px; margin-top: 10px; border: 1px solid #ccc;">
            <strong>readme.txt Preview (first 500 chars):</strong><br>
            <pre><?php echo esc_html(substr($readme_txt_content, 0, 500)) . '...'; ?></pre>
        </div>
        <?php endif; ?>
    </div>
    <?php
    
    // Add recommendation if needed
    if (!$readme_txt_exists) {
        ?>
        <div class="notice notice-warning is-dismissible">
            <p><strong>Recommendation:</strong> Your readme.txt file is missing. This is required for the "View Details" link to work properly. Please deactivate and reactivate the plugin to generate it.</p>
        </div>
        <?php
    }
}

/**
 * Generate readme.txt on plugin activation and admin page loads
 */
function force_readme_txt_generation() {
    // Run on admin pages and during AJAX calls
    if (!is_admin() && !wp_doing_ajax()) {
        return;
    }
    
    // Check if readme.txt exists
    $readme_txt_path = plugin_dir_path(__FILE__) . 'readme.txt';
    $readme_md_path = plugin_dir_path(__FILE__) . 'README.md';
    
    if (!file_exists($readme_txt_path) && file_exists($readme_md_path)) {
        global $plugin_slug, $github_repo_url, $github_branch;
        
        // Create an instance to convert README.md to readme.txt
        require_once plugin_dir_path(__FILE__) . 'includes/class-github-plugin-info.php';
        $updater = new GitHub_Plugin_Updater(
            __FILE__,
            $plugin_slug,
            $github_repo_url,
            $github_branch,
            null,
            []
        );
        
        // Manually trigger the conversion
        $updater->convert_readme_to_txt();
    }
}
// Run on admin page loads and explicitly during plugin information API requests
add_action('admin_init', 'force_readme_txt_generation');
add_action('plugins_api', 'force_readme_txt_generation', 1); // Run before plugins_api is processed
add_action('after_plugin_row', 'force_readme_txt_generation'); // Run when plugins list is displayed
?>