document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('frost-date-form');
    const resultContainer = document.getElementById('frost-date-results');

    form.addEventListener('submit', function(event) {
        event.preventDefault();
        const zipcode = document.getElementById('zipcode').value;

        fetchFrostDates(zipcode);
    });

    function fetchFrostDates(zipcode) {
        fetch(`https://your-api-endpoint.com/frost-dates?zipcode=${zipcode}`)
            .then(response => response.json())
            .then(data => {
                displayResults(data);
            })
            .catch(error => {
                console.error('Error fetching frost dates:', error);
                resultContainer.innerHTML = '<p>Error retrieving data. Please try again later.</p>';
            });
    }

    function displayResults(data) {
        if (data && data.average && data.max && data.min) {
            resultContainer.innerHTML = `
                <h3>Frost-Free Dates for Zipcode: ${data.zipcode}</h3>
                <p>Average Frost-Free Date: ${data.average}</p>
                <p>Maximum Frost-Free Date (Last 15 Years): ${data.max.last15}</p>
                <p>Minimum Frost-Free Date (Last 15 Years): ${data.min.last15}</p>
                <p>Maximum Frost-Free Date (Last 30 Years): ${data.max.last30}</p>
                <p>Minimum Frost-Free Date (Last 30 Years): ${data.min.last30}</p>
            `;
        } else {
            resultContainer.innerHTML = '<p>No data available for the provided zipcode.</p>';
        }
    }
});