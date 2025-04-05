<?php

class Frost_Date_Activator {

    public static function activate() {
        // Set default options or perform any necessary setup on activation
        // For example, you might want to create options in the database
        add_option('frost_date_default_option', 'value');
    }
}