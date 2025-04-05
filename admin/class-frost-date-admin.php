<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Frost_Date_Admin {
    private $api;

    public function __construct() {
        $this->api = new Frost_Date_API();
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    public function add_admin_menu() {
        add_menu_page(
            'Frost Date Lookup',
            'Frost Dates',
            'manage_options',
            'frost-date-lookup',
            array( $this, 'create_admin_page' ),
            'dashicons-calendar-alt',
            100
        );
    }

    public function create_admin_page() {
        include_once plugin_dir_path( __FILE__ ) . 'partials/admin-display.php';
    }

    public function register_settings() {
        register_setting( 'frost_date_options_group', 'frost_date_zipcode' );
    }

    public function get_frost_dates( $zipcode ) {
        $data = $this->api->fetch_frost_dates( $zipcode );
        return $data;
    }
}
?>