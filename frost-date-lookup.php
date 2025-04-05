<?php
/**
 * Plugin Name: Frost Date Lookup
 * Description: A plugin to retrieve average frost-free dates based on zip code using NOAA/NWS data.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * License: GPL2
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

// Update checker
require_once plugin_dir_path( __FILE__ ) . 'vendor/plugin-update-checker/plugin-update-checker.php';
$update_checker = Puc_v4_Factory::buildUpdateChecker(
    'https://github.com/yourusername/frost-date-lookup',
    __FILE__,
    'frost-date-lookup'
);
$update_checker->setBranch('main'); // Set the branch to check for updates
?>