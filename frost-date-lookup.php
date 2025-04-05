<?php
/**
 * Plugin Name: Frost Date Lookup
 * Description: A plugin to retrieve average frost-free dates based on zip code using NOAA/NWS data.
 * Version: 1.0.6
 * Author: Everette Mills
 * Author URI: https://blueboatsolutions.com
 * License: GPL2
 * 
 * Changelog:
 * 1.0.6 - Realigned components and system updates
 * 1.0.5 - Version update and plugin improvements
 * 1.0.1 - Fixed bug with data retrieval and improved stability of API requests
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Include necessary files
require_once plugin_dir_path( __FILE__ ) . 'includes/class-frost-date-loader.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-frost-date-activator.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-frost-date-deactivator.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-frost-date-i18n.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-frost-date.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-frost-date-api.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-frost-date-updater.php';

// Activation and deactivation hooks
register_activation_hook( __FILE__, array( 'Frost_Date_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Frost_Date_Deactivator', 'deactivate' ) );

// Initialize the plugin
function run_frost_date_lookup() {
    $plugin = new Frost_Date_Loader();
    $plugin->run();
}
run_frost_date_lookup();

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
    $myUpdateChecker->setBranch('stable-branch-name');
}
?>