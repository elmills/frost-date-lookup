<?php
/**
 * Plugin Name: Frost Date Lookup
 * Description: A plugin to retrieve average frost-free dates based on zip code using NOAA/NWS data.
 * Version: 1.0.11
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
require_once FROST_DATE_LOOKUP_PATH . 'includes/class-frost-date-lookup.php';
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

/**
 * Initialize the GitHub plugin info handler
 * It's best to do this early but after all required WP functions are available
 */
function frost_date_lookup_init_github_info() {
    new Github_Plugin_Info(
        'frost-date-lookup',      // Plugin slug (should match directory name)
        __FILE__,                 // Plugin main file (__FILE__ gives full path)
        'elmills',                // GitHub username
        'frost-date-lookup'       // GitHub repository name
    );
}
add_action('plugins_loaded', 'frost_date_lookup_init_github_info');

function run_frost_date_lookup() {
    $plugin = new Frost_Date_Loader();
    $plugin->run();
}
run_frost_date_lookup();

// Enqueue scripts and styles
function frost_date_lookup_enqueue_scripts() {
    wp_enqueue_style('frost-date-lookup-style', FROST_DATE_LOOKUP_URL . 'assets/css/frost-date-lookup.css', array(), '1.0.10');
    wp_enqueue_script('frost-date-lookup-script', FROST_DATE_LOOKUP_URL . 'assets/js/frost-date-lookup.js', array('jquery'), '1.0.10', true);
    
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
// Update checker - safely include if available
$update_checker_path = plugin_dir_path( __FILE__ ) . 'vendor/plugin-update-checker/plugin-update-checker.php';
if ( file_exists( $update_checker_path ) ) {
    require 'vendor/plugin-update-checker/plugin-update-checker.php';
    
    // Create the update checker without the namespace import
    $myUpdateChecker = YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
        'https://github.com/elmills/frost-date-lookup/',
        __FILE__,
        'frost-date-lookup'
    );
    
    // Set the branch that contains the stable release.
    $myUpdateChecker->setBranch('main'); // Use your primary branch name (main or master)
    
    // Optional: If using release assets for distribution
    $myUpdateChecker->getVcsApi()->enableReleaseAssets();
    
    // Provide custom plugin information for the update checker
    $myUpdateChecker->addResultFilter(function ($info) {
        $info->sections = array(
            'description' => 'A plugin to retrieve average frost-free dates based on zip code using NOAA/NWS data.',
            'installation' => 'Install the plugin and activate it. Use the shortcode [frost_date_lookup] on any page or post.',
            'changelog' => '<h4>1.0.11</h4><ul><li>Latest improvements</li></ul>'
        );
        return $info;
    });
}

?>