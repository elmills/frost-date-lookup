<?php
/**
 * Displays the frost-free date information to users.
 */

function display_frost_free_dates($zipcode) {
    // Fetch data from the API
    $api = new Frost_Date_API();
    $data = $api->get_frost_free_dates($zipcode);

    if (is_wp_error($data)) {
        return '<p>Error retrieving data. Please try again later.</p>';
    }

    $average_date = $data['average'];
    $max_date_15_years = $data['max_15_years'];
    $min_date_15_years = $data['min_15_years'];
    $max_date_30_years = $data['max_30_years'];
    $min_date_30_years = $data['min_30_years'];

    ob_start();
    ?>
    <div class="frost-free-dates">
        <h2>Frost-Free Dates for Zipcode: <?php echo esc_html($zipcode); ?></h2>
        <ul>
            <li>Average Frost-Free Date: <?php echo esc_html($average_date); ?></li>
            <li>Max Frost-Free Date (Last 15 Years): <?php echo esc_html($max_date_15_years); ?></li>
            <li>Min Frost-Free Date (Last 15 Years): <?php echo esc_html($min_date_15_years); ?></li>
            <li>Max Frost-Free Date (Last 30 Years): <?php echo esc_html($max_date_30_years); ?></li>
            <li>Min Frost-Free Date (Last 30 Years): <?php echo esc_html($min_date_30_years); ?></li>
        </ul>
    </div>
    <?php
    return ob_get_clean();
}

// Example usage
// echo display_frost_free_dates('90210');
?>