<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

require_once dirname( __FILE__ ) . '/../../vendor/plugin-update-checker/plugin-update-checker.php';

class Frost_Date_Updater {
    private $update_checker;

    public function __construct() {
        $this->update_checker = Puc_v4_Factory::buildUpdateChecker(
            'https://github.com/yourusername/frost-date-lookup', // Replace with your GitHub repository URL
            __FILE__,
            'frost-date-lookup'
        );

        // Optional: Set the branch that should be checked for updates
        $this->update_checker->setBranch('main'); // Replace with your branch name if different
    }

    public function init() {
        // You can add additional initialization code here if needed
    }
}
?>