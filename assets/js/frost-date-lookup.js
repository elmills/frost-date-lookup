jQuery(document).ready(function($) {
    $('#frost-date-lookup-form').on('submit', function(e) {
        e.preventDefault();
        
        var zipCode = $('#zip-code').val();
        
        // Show loading indicator
        $('#lookup-button').text('Loading...');
        
        // Make AJAX request
        $.ajax({
            url: frost_date_lookup.ajax_url,
            type: 'POST',
            data: {
                action: 'get_frost_dates',
                nonce: frost_date_lookup.nonce,
                zip_code: zipCode
            },
            success: function(response) {
                // Reset button text
                $('#lookup-button').text('Find Frost Dates');
                
                if (response.success) {
                    // Display the results
                    $('#frost-date-results').show();
                    $('.results-container').html(response.data.html);
                } else {
                    // Show error message
                    alert(response.data.message || 'Error retrieving frost dates.');
                }
            },
            error: function() {
                // Reset button text
                $('#lookup-button').text('Find Frost Dates');
                alert('An error occurred. Please try again.');
            }
        });
    });
});
