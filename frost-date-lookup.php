<?php
/**
 * Plugin Name: Frost Date Lookup
 * Plugin URI: https://github.com/elmills/frost-date-lookup
 * Description: A plugin to retrieve average frost-free dates based on zip code using NOAA/NWS data.
 * Version: 1.0.29
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
require_once plugin_dir_path( __FILE__ ) . 'includes/class-github-to-wordpress-readme-converter.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-github-readme-updater.php';


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
    wp_enqueue_style('frost-date-lookup-style', FROST_DATE_LOOKUP_URL . 'assets/css/frost-date-lookup.css', array(), '1.0.29');
    wp_enqueue_script('frost-date-lookup-script', FROST_DATE_LOOKUP_URL . 'assets/js/frost-date-lookup.js', array('jquery'), '1.0.29', true);
    
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

// Initialize the GitHub Readme Updater
// This needs to be after all constants and includes are defined
$github_updater = new GitHub_Readme_Updater(
    __FILE__,                // plugin_file
    $plugin_slug,            // plugin_slug
    $github_repo_url,        // github_repo_url
    $github_branch,          // github_branch
    $github_token,           // github_token
    $plugin_metadata,        // plugin_metadata
    $updater_class_path,     // updater_class_path
    $library_path,           // library_path
    $text_domain             // text_domain
);
?>