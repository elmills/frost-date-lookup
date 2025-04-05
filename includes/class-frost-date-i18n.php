<?php
class Frost_Date_i18n {
    public static function load_plugin_textdomain() {
        load_plugin_textdomain( 'frost-date-lookup', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }
}
?>