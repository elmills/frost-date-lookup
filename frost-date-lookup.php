<?php
/**
 * Plugin Name: Frost Date Lookup
 * Plugin URI: https://github.com/elmills/frost-date-lookup
 * Description: A plugin to retrieve average frost-free dates based on zip code using NOAA/NWS data.
 * Version: 1.0.24
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
    wp_enqueue_style('frost-date-lookup-style', FROST_DATE_LOOKUP_URL . 'assets/css/frost-date-lookup.css', array(), '1.0.24');
    wp_enqueue_script('frost-date-lookup-script', FROST_DATE_LOOKUP_URL . 'assets/js/frost-date-lookup.js', array('jquery'), '1.0.24', true);
    
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
 * GitHub Plugin Updater initialization
 * 
 * This implementation directly connects to GitHub to check for updates
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
    'contributors' => 'elmills',
    // 'donate_link' => 'https://example.com/donate', // Optional - uncomment if needed
    'tags' => 'frost date, lookup, wordpress, zipcode, garden',
    'requires_php' => '8.1'
];

// Paths to required files (change if your structure is different)
$updater_class_path = 'includes/class-github-plugin-info.php';
$library_path = 'plugin-update-checker/plugin-update-checker.php';

// Text domain for translations
$text_domain = 'frost-date-lookup';

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
    
    if (!function_exists('get_plugin_data')) {
        require_once(ABSPATH . 'wp-admin/includes/plugin.php');
    }
    
    // Define the plugin file constant - this is critical!
    $plugin_file = __FILE__;
    
    // Check if the GitHub Plugin Updater class exists
    $updater_class_full_path = plugin_dir_path($plugin_file) . $updater_class_path;
    if (!file_exists($updater_class_full_path)) {
        add_action('admin_notices', function() use ($text_domain, $updater_class_full_path) {
            ?>
            <div class="notice notice-error">
                <p><?php printf(
                    esc_html__('GitHub Plugin Updater class file is missing! Plugin updates will not work correctly. Please add the file to: %s', $text_domain),
                    esc_html($updater_class_full_path)
                ); ?></p>
            </div>
            <?php
        });
        return;
    }
    
    // Include the GitHub Plugin Updater class
    require_once $updater_class_full_path;
    
    // Check if the Plugin Update Checker library exists
    $library_full_path = plugin_dir_path($plugin_file) . $library_path;
    if (!file_exists($library_full_path)) {
        add_action('admin_notices', function() use ($text_domain, $library_full_path) {
            ?>
            <div class="notice notice-error">
                <p>
                    <?php esc_html_e('Plugin Update Checker library is missing! Please download it from', $text_domain); ?>
                    <a href="https://github.com/YahnisElsts/plugin-update-checker" target="_blank">GitHub</a>
                    <?php printf(
                        esc_html__('and add it to: %s', $text_domain),
                        esc_html($library_full_path)
                    ); ?>
                </p>
            </div>
            <?php
        });
        return;
    }
    
    // Check for class existence
    if (!class_exists('GitHub_Plugin_Updater')) {
        add_action('admin_notices', function() use ($text_domain) {
            ?>
            <div class="notice notice-error">
                <p><?php esc_html_e('GitHub Plugin Updater class not found! Check your implementation.', $text_domain); ?></p>
            </div>
            <?php
        });
        return;
    }
    
    // Check if README.md exists
    $readme_path = plugin_dir_path($plugin_file) . 'README.md';
    if (!file_exists($readme_path)) {
        add_action('admin_notices', function() use ($text_domain) {
            ?>
            <div class="notice notice-warning">
                <p><?php esc_html_e('README.md file is missing! Plugin update details may not display correctly.', $text_domain); ?></p>
            </div>
            <?php
        });
    }
    
    try {
        // Get plugin data for metadata
        $plugin_data = get_plugin_data($plugin_file);
        
        // Merge plugin data with custom metadata
        $combined_metadata = array_merge($plugin_metadata, [
            'plugin_name' => $plugin_data['Name'],
            'plugin_version' => $plugin_data['Version'],
            'plugin_author' => $plugin_data['Author'],
            'plugin_description' => $plugin_data['Description']
        ]);
        
        // Create a new updater instance with explicit parameters
        $updater = new GitHub_Plugin_Updater(
            $plugin_file,
            $plugin_slug,
            $github_repo_url,
            $github_branch,
            $github_token,
            $combined_metadata
        );
        
        // Force readme.txt generation
        $updater->convert_readme_to_txt();
        
        // Force clear update cache to ensure fresh check
        delete_site_transient('update_plugins');
        delete_site_transient('puc_check_count_' . $plugin_slug);
        delete_site_transient('puc_request_info_' . $plugin_slug);
        wp_update_plugins();
        
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

// Hook the initialization - use a lower priority to ensure all dependencies are loaded
add_action('plugins_loaded', 'initialize_github_updater', 20);

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
    $plugin_file = plugin_basename(__FILE__);
    $has_update = isset($update_data->response[$plugin_file]);
    
    // Get information about available version
    $available_version = $has_update ? $update_data->response[$plugin_file]->new_version : 'Unknown';
    
    // If still unknown, try alternative keys or search for it
    if ($available_version === 'Unknown' && is_object($update_data) && !empty($update_data->response)) {
        foreach ($update_data->response as $key => $data) {
            if (strpos($key, $plugin_slug) !== false) {
                $has_update = true;
                $available_version = $data->new_version;
                break;
            }
        }
    }
    
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
 * Generate readme.txt for plugin updates
 * 
 * This function generates the readme.txt file from README.md, but includes
 * performance optimizations to prevent excessive regeneration.
 */
function force_readme_txt_generation($force = false) {
    // Check if we've recently generated the file (within last hour)
    // Skip this check if force = true
    if (!$force && get_transient('readme_txt_generated')) {
        return;
    }
    
    $readme_md_path = plugin_dir_path(__FILE__) . 'README.md';
    $readme_txt_path = plugin_dir_path(__FILE__) . 'readme.txt';
    
    // Only regenerate if:
    // 1. README.md exists, AND
    // 2. readme.txt doesn't exist OR force parameter is true
    if (file_exists($readme_md_path) && ($force || !file_exists($readme_txt_path))) {
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
        
        // Set transient to prevent frequent regeneration
        set_transient('readme_txt_generated', true, HOUR_IN_SECONDS);
        
        // Only clear update cache if we actually generated a new file
        if (file_exists($readme_txt_path)) {
            delete_site_transient('update_plugins');
            delete_site_transient('puc_check_count_' . $plugin_slug);
            delete_site_transient('puc_request_info_' . $plugin_slug);
        }
    }
}

// Run specifically during plugin information API requests (when WP checks for updates)
add_action('plugins_api', function($result, $action, $args) {
    // Only run for our plugin
    if ($action === 'plugin_information' && isset($args->slug) && $args->slug === $GLOBALS['plugin_slug']) {
        force_readme_txt_generation(true); // Force regeneration during plugin info requests
    }
    return $result;
}, 1, 3);

// When viewing plugins list, check readme.txt but don't force regeneration
add_action('after_plugin_row', function($plugin_file, $plugin_data) {
    if (plugin_basename(__FILE__) === $plugin_file) {
        force_readme_txt_generation(false);
    }
}, 10, 2);

// Add hook to WP update check process
add_action('update_option_update_plugins', function() {
    force_readme_txt_generation(true);
});
?>