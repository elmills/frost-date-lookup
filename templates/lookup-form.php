<div class="frost-date-lookup-container">
    <form id="frost-date-lookup-form" class="frost-date-lookup-form">
        <div class="form-group">
            <label for="zip-code">Enter Zip Code:</label>
            <input type="text" id="zip-code" name="zip_code" placeholder="e.g. 12345" required>
        </div>
        <div class="form-group">
            <button type="submit" id="lookup-button">Find Frost Dates</button>
        </div>
    </form>
    
    <div id="frost-date-results" class="frost-date-results" style="display: none;">
        <h3>Frost Date Results</h3>
        <div class="results-container">
            <!-- Results will be displayed here via JavaScript -->
        </div>
    </div>
</div>
