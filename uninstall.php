<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Clean up options and data when the plugin is uninstalled
delete_option( 'frost_date_lookup_options' );
delete_option( 'frost_date_lookup_data' );

// If you have custom database tables, you can drop them here
global $wpdb;
$table_name = $wpdb->prefix . 'frost_date_lookup';
$wpdb->query( "DROP TABLE IF EXISTS $table_name" );
?>