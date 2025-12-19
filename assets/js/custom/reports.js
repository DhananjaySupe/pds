$(document).ready(function () {
    // Show/hide entity select based on report type
    $('#report-type').on('change', function () {
        var reportType = $(this).val();

        // Hide all selects
        $('#vendor-select-container').hide();
        $('#godown-select-container').hide();
        $('#shop-select-container').hide();

        // Clear required attributes
        $('#vendor-select').removeAttr('required');
        $('#godown-select').removeAttr('required');
        $('#shop-select').removeAttr('required');

        // Show and set required for selected type
        if (reportType === 'vendor') {
            $('#vendor-select-container').show();
            $('#vendor-select').attr('required', 'required');
        } else if (reportType === 'godown') {
            $('#godown-select-container').show();
            $('#godown-select').attr('required', 'required');
        } else if (reportType === 'shop') {
            $('#shop-select-container').show();
            $('#shop-select').attr('required', 'required');
        }
    });

    // Update form action based on report type
    $('#report-form').on('submit', function (e) {
        var reportType = $('#report-type').val();
        var entityId = 0;
        var entityType = '';

        if (reportType === 'vendor') {
            entityId = $('#vendor-select').val();
            entityType = 'vendor';
        } else if (reportType === 'godown') {
            entityId = $('#godown-select').val();
            entityType = 'godown';
        } else if (reportType === 'shop') {
            entityId = $('#shop-select').val();
            entityType = 'shop';
        }

        if (entityId && entityType) {
            // Update form action to include entity info
            var params = new URLSearchParams();
            params.set('type', reportType);
            params.set('entity_type', entityType);
            params.set('entity_id', entityId);

            var startDate = $('input[name="start_date"]').val();
            var endDate = $('input[name="end_date"]').val();
            if (startDate) params.set('start_date', startDate);
            if (endDate) params.set('end_date', endDate);

            // Redirect to reports index with params, which will show the action buttons
            window.location.href = SITE_URL + '/reports?' + params.toString();
            e.preventDefault();
            return false;
        }
    });

    // Initialize on page load
    $('#report-type').trigger('change');
});

