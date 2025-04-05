<?php
/**
 * Plugin Name: Frost Date Lookup
 * Description: A plugin to retrieve average frost-free dates based on zip code using NOAA/NWS data.
 * Version: 1.0.10
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
            'changelog' => '<h4>1.0.10</h4><ul><li>Latest improvements</li></ul>'
        );
        return $info;
    });
}

/**
 * Custom Plugin Information
 */
// Add filter to provide custom plugin information
add_filter('plugins_api', 'frost_date_lookup_plugin_info', 10, 3);

function frost_date_lookup_plugin_info($res, $action, $args) {
    // Check if this is a request for our plugin
    if ($action == 'plugin_information' && isset($args->slug) && $args->slug == 'frost-date-lookup') {
        $plugin_data = get_plugin_data(__FILE__);
        
        $res = new stdClass();
        $res->name = $plugin_data['Name'];
        $res->slug = 'frost-date-lookup';
        $res->version = $plugin_data['Version'];
        $res->author = $plugin_data['Author'];
        $res->author_profile = 'https://blueboatsolutions.com';
        $res->requires = '6.0';  // Minimum WordPress version required
        $res->tested = '6.4';    // WordPress version tested up to
        $res->last_updated = date('Y-m-d');  // Today's date as last update
        $res->download_link = 'https://github.com/elmills/frost-date-lookup/releases/';
        $res->sections = array(
            'description' => $plugin_data['Description'],
            'installation' => 'Install the plugin and activate it. Use the shortcode [frost_date_lookup] on any page or post.',
            'changelog' => '<h4>1.0.10</h4><ul><li>Latest improvements</li></ul>'
        );
        
        return $res;
    }
    
    // Not our plugin, let WordPress handle it
    return $res;
}
?>