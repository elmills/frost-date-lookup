<?php

class Frost_Date_Public {

    private $frost_date_api;

    public function __construct() {
        $this->frost_date_api = new Frost_Date_API();
        add_shortcode('frost_free_dates', [$this, 'render_frost_free_dates']);
    }

    public function render_frost_free_dates($atts) {
        $atts = shortcode_atts(['zipcode' => ''], $atts);
        $zipcode = sanitize_text_field($atts['zipcode']);

        if (empty($zipcode)) {
            return 'Please provide a valid zipcode.';
        }

        $data = $this->frost_date_api->get_frost_free_dates($zipcode);

        if (is_wp_error($data)) {
            return 'Error retrieving data: ' . $data->get_error_message();
        }

        return $this->generate_output($data);
    }

    private function generate_output($data) {
        $output = '<div class="frost-free-dates">';
        $output .= '<h2>Frost-Free Dates for Zipcode: ' . esc_html($data['zipcode']) . '</h2>';
        $output .= '<p>Average Frost-Free Date: ' . esc_html($data['average_date']) . '</p>';
        $output .= '<p>Max Frost-Free Date (Last 15 Years): ' . esc_html($data['max_date_15_years']) . '</p>';
        $output .= '<p>Min Frost-Free Date (Last 15 Years): ' . esc_html($data['min_date_15_years']) . '</p>';
        $output .= '<p>Max Frost-Free Date (Last 30 Years): ' . esc_html($data['max_date_30_years']) . '</p>';
        $output .= '<p>Min Frost-Free Date (Last 30 Years): ' . esc_html($data['min_date_30_years']) . '</p>';
        $output .= '</div>';

        return $output;
    }
}