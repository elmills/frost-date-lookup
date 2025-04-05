<?php
/**
 * Main class for the Frost Date Lookup plugin
 */
class Frost_Date_Lookup {
    
    /**
     * Initialize the plugin
     */
    public function init() {
        // Set up AJAX handlers
        add_action('wp_ajax_get_frost_dates', array($this, 'get_frost_dates'));
        add_action('wp_ajax_nopriv_get_frost_dates', array($this, 'get_frost_dates'));
    }
    
    /**
     * AJAX handler for getting frost dates
     */
    public function get_frost_dates() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'frost_date_lookup_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed.'));
        }
        
        // Get zip code
        $zip_code = isset($_POST['zip_code']) ? sanitize_text_field($_POST['zip_code']) : '';
        
        if (empty($zip_code)) {
            wp_send_json_error(array('message' => 'Please enter a valid zip code.'));
        }
        
        // Here you would typically make a request to your API to get the frost dates
        // For now, just return some dummy data
        $html = '<div class="frost-date-info">';
        $html .= '<p><strong>Location:</strong> ' . $zip_code . '</p>';
        $html .= '<p><strong>Last Spring Frost Date (Average):</strong> April 15</p>';
        $html .= '<p><strong>First Fall Frost Date (Average):</strong> October 20</p>';
        $html .= '<p><strong>Growing Season Length:</strong> 188 days</p>';
        $html .= '</div>';
        
        wp_send_json_success(array('html' => $html));
    }
}
