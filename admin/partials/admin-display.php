<?php
// This file contains the HTML markup for the admin display of the Frost Date Lookup plugin.

?>

<div class="wrap">
    <h1><?php esc_html_e('Frost Date Lookup Settings', 'frost-date-lookup'); ?></h1>
    
    <form method="post" action="options.php">
        <?php
        settings_fields('frost_date_options_group');
        do_settings_sections('frost_date_lookup');
        ?>
        
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php esc_html_e('Zip Code', 'frost-date-lookup'); ?></th>
                <td>
                    <input type="text" name="frost_date_zipcode" value="<?php echo esc_attr(get_option('frost_date_zipcode')); ?>" />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php esc_html_e('Average Frost-Free Date', 'frost-date-lookup'); ?></th>
                <td>
                    <input type="text" name="frost_date_average" value="<?php echo esc_attr(get_option('frost_date_average')); ?>" readonly />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php esc_html_e('Max Frost-Free Date (Last 15 Years)', 'frost-date-lookup'); ?></th>
                <td>
                    <input type="text" name="frost_date_max_15" value="<?php echo esc_attr(get_option('frost_date_max_15')); ?>" readonly />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php esc_html_e('Min Frost-Free Date (Last 15 Years)', 'frost-date-lookup'); ?></th>
                <td>
                    <input type="text" name="frost_date_min_15" value="<?php echo esc_attr(get_option('frost_date_min_15')); ?>" readonly />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php esc_html_e('Max Frost-Free Date (Last 30 Years)', 'frost-date-lookup'); ?></th>
                <td>
                    <input type="text" name="frost_date_max_30" value="<?php echo esc_attr(get_option('frost_date_max_30')); ?>" readonly />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php esc_html_e('Min Frost-Free Date (Last 30 Years)', 'frost-date-lookup'); ?></th>
                <td>
                    <input type="text" name="frost_date_min_30" value="<?php echo esc_attr(get_option('frost_date_min_30')); ?>" readonly />
                </td>
            </tr>
        </table>
        
        <?php submit_button(); ?>
    </form>
</div>