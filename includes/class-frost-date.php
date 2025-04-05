<?php
class Frost_Date {
    private $api;

    public function __construct() {
        $this->api = new Frost_Date_API();
    }

    public function get_frost_free_dates($zipcode) {
        $data = $this->api->fetch_frost_free_data($zipcode);
        if (is_wp_error($data)) {
            return $data;
        }

        $average_date = $this->calculate_average_date($data);
        $max_date_15_years = $this->get_max_date($data, 15);
        $min_date_15_years = $this->get_min_date($data, 15);
        $max_date_30_years = $this->get_max_date($data, 30);
        $min_date_30_years = $this->get_min_date($data, 30);

        return [
            'average_date' => $average_date,
            'max_date_15_years' => $max_date_15_years,
            'min_date_15_years' => $min_date_15_years,
            'max_date_30_years' => $max_date_30_years,
            'min_date_30_years' => $min_date_30_years,
        ];
    }

    private function calculate_average_date($data) {
        $total = 0;
        $count = count($data);
        foreach ($data as $date) {
            $total += strtotime($date);
        }
        return date('Y-m-d', $total / $count);
    }

    private function get_max_date($data, $years) {
        $filtered_data = array_filter($data, function($date) use ($years) {
            return (date('Y', strtotime($date)) >= (date('Y') - $years));
        });
        return max($filtered_data);
    }

    private function get_min_date($data, $years) {
        $filtered_data = array_filter($data, function($date) use ($years) {
            return (date('Y', strtotime($date)) >= (date('Y') - $years));
        });
        return min($filtered_data);
    }
}
?>