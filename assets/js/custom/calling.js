var callingGrid;
var nRecordsTotal = 0;
var nRecordsFiltered = 0;
var oFilter = {
    keywords: '',
    center_id: '',
    gender: '',
    date_range: ''
};

$(document).ready(function () {
    // Initialize DataTable
    callingGrid = $('#calling-grid').DataTable({
        responsive: true,
        autoWidth: true,
        lengthChange: false,
        searching: false,
        scrollY: "calc(100vh - 400px)",
        scrollX: "100%",
        scrollCollapse: false,
        processing: false,
        serverSide: true,
        sorting: [],
        order: [[4, 'asc']], // Sort by created_at ASC
        paging: true,
        deferRender: true,
        pageLength: 50,
        stateSave: true,
        ajax: {
            type: "POST",
            url: SITE_URL + "/calling",
            beforeSend: function () {
                showLoader('.page-content', 'callingLoader');
            },
            complete: function () {
                hideLoader('callingLoader');
            },
            data: function (d) {
                d.keywords = oFilter.keywords;
                d.center_id = oFilter.center_id;
                d.gender = oFilter.gender;
                d.date_range = oFilter.date_range;
                d.recordstotal = nRecordsTotal;
                d.recordsfiltered = nRecordsFiltered;
            },
            dataSrc: function (json) {
                nRecordsTotal = parseInt(json.recordsTotal);
                nRecordsFiltered = parseInt(json.recordsFiltered);
                return json.data;
            }
        },
        columns: [
            { data: "image", width: "80px", orderable: false },
            { data: "param1", width: "150px", orderable: false },
            { data: "param2", width: "120px", orderable: false },
            { data: "param3", width: "150px", orderable: false },
            { data: "param4", width: "150px", orderable: false },
            { data: "param5", width: "120px", orderable: false },
            { data: "param6", width: "150px", orderable: false },
            { data: "actions", width: "200px", orderable: false, class: "text-center" },
        ],
        pagingType: "full_numbers",
        language: {
            paginate: {
                previous: "<i class='mdi mdi-chevron-left'>",
                next: "<i class='mdi mdi-chevron-right'>"
            }
        },
        drawCallback: function () {
            $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
        }
    });

    // Set default date to today
    $('#call_date').val(new Date().toISOString().split('T')[0]);
    $('#call_time').val(new Date().toTimeString().slice(0, 5));

    // Handle call status change
    $('#call_status').change(function() {
        if ($(this).val() === 'confirmed_found') {
            $('#reuniteInfo').show();
        } else {
            $('#reuniteInfo').hide();
        }
    });

    // Handle update status change
    $('#update_call_status').change(function() {
        if ($(this).val() === 'confirmed_found') {
            // Show reunite fields in update modal
            if (!$('#updateReuniteInfo').length) {
                $('#updateStatusForm').append(`
                    <div id="updateReuniteInfo">
                        <hr>
                        <h6>Reunite Information</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="update_receiver_type" class="form-label">Receiver Type</label>
                                    <select class="form-control" id="update_receiver_type" name="receiver_type">
                                        <option value="Police">Police</option>
                                        <option value="Ngo">Ngo</option>
                                        <option value="Relative" selected>Relative</option>
                                        <option value="Friend">Friend</option>
                                        <option value="Neighbor">Neighbor</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="update_receiver_address" class="form-label">Receiver Address</label>
                                    <input type="text" class="form-control" id="update_receiver_address" name="receiver_address" placeholder="Enter receiver address">
                                </div>
                            </div>
                        </div>
                    </div>
                `);
            }
            $('#updateReuniteInfo').show();
        } else {
            $('#updateReuniteInfo').hide();
        }
    });

    // Bootstrap validation for forms
    $('#callLogForm').on('submit', function(e) {
        e.preventDefault();
        if (this.checkValidity()) {
            saveCallLog();
        } else {
            e.stopPropagation();
        }
        $(this).addClass('was-validated');
    });

    $('#updateStatusForm').on('submit', function(e) {
        e.preventDefault();
        if (this.checkValidity()) {
            saveStatusUpdate();
        } else {
            e.stopPropagation();
        }
        $(this).addClass('was-validated');
    });

    // Reset validation on modal close
    $('#callLogModal').on('hidden.bs.modal', function() {
        $('#callLogForm').removeClass('was-validated');
        $('#callLogForm')[0].reset();
        $('#reuniteInfo').hide();
    });

    $('#updateStatusModal').on('hidden.bs.modal', function() {
        $('#updateStatusForm').removeClass('was-validated');
        $('#updateStatusForm')[0].reset();
        $('#updateReuniteInfo').remove();
    });

    $(function() {
        //var start = moment().subtract(1, 'days');
        var start = moment().subtract(3, 'months');
        var end = moment();

        function cd(start, end) {
            $('#date_range').val(start.format('DD MMM YYYY') + ' - ' + end.format('DD MMM YYYY'));
        }

        function cb(start, end) {
            $('#date_range').val(start.format('DD MMM YYYY') + ' - ' + end.format('DD MMM YYYY'));
            // Update the filter and reload the grid
            oFilter.date_range = start.format('DD MMM YYYY') + ' - ' + end.format('DD MMM YYYY');
            callingGrid.ajax.reload();
        }

        $('#date_range').daterangepicker({
            startDate: start,
            endDate: end,
            alwaysShowCalendars : true,
            locale: {
                format: 'DD MMM YYYY',
                separator: ' - ',
                applyLabel: 'Apply',
                cancelLabel: 'Cancel',
                fromLabel: 'From',
                toLabel: 'To',
                customRangeLabel: 'Custom',
                weekLabel: 'W',
                daysOfWeek: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
                monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                firstDay: 1
            },
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'This Week': [moment().startOf('week'), moment().endOf('week')],
                'Last Week': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'This Year': [moment().startOf('year'), moment().endOf('year')],
                'Last Year': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')]
            }
        }, cb, cd);

        cd(start, end);

        // Set initial filter value
        oFilter.date_range = start.format('DD MMM YYYY') + ' - ' + end.format('DD MMM YYYY');
        callingGrid.ajax.reload();
    });

    // Add automatic filtering on input changes
    $('#keywords').on('input', function() {
        oFilter.keywords = $(this).val();
        callingGrid.ajax.reload();
    });

    $('#center_id').on('change', function() {
        oFilter.center_id = $(this).val();
        callingGrid.ajax.reload();
    });

    $('#gender').on('change', function() {
        oFilter.gender = $(this).val();
        callingGrid.ajax.reload();
    });

    // Date range is already handled by the daterangepicker callback

});

// Clear filters
function clearFilters() {
    $('#callingFilterForm')[0].reset();

    // Reset date range picker to last 3 months
    var start = moment().subtract(3, 'months');
    var end = moment();
    $('#date_range').val(start.format('DD MMM YYYY') + ' - ' + end.format('DD MMM YYYY'));

    oFilter = {
        keywords: '',
        center_id: '',
        gender: '',
        date_range: start.format('DD MMM YYYY') + ' - ' + end.format('DD MMM YYYY')
    };
    callingGrid.ajax.reload();
}

// Make call function
function makeCall(lostPersonId) {
    $('#lost_id').val(lostPersonId);
    $('#call_date').val(new Date().toISOString().split('T')[0]);
    $('#call_time').val(new Date().toTimeString().slice(0, 5));

    // Reset form and set default values
    $('#callLogForm')[0].reset();
    $('#callLogForm').removeClass('was-validated');

    // Set default values for receiver fields
    $('#receiver_name').val('Family Member');
    $('#receiver_relation').val('Family');
    $('#receiver_mobile').val('Same as complainant');
    $('#receiver_address').val('');

    $('#reuniteInfo').hide();

    $('#callLogModal').modal('show');
}

// Save call log
function saveCallLog() {
    var form = $('#callLogForm')[0];

    if (!form.checkValidity()) {
        form.reportValidity();
        $('#callLogForm').addClass('was-validated');
        return;
    }

    var formData = new FormData(form);

    $.ajax({
        url: SITE_URL + '/calling/save-call-log',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function() {
            showLoader('#callLogModal .modal-content', 'saveCallLoader');
        },
        success: function(response) {
            hideLoader('saveCallLoader');
            if (response.success) {
                $('#callLogModal').modal('hide');
                callingGrid.ajax.reload();
                showSuccess(response.message);
            } else {
                showError(response.message);
            }
        },
        error: function() {
            hideLoader('saveCallLoader');
            showError('An error occurred while saving call log.');
        }
    });
}

// View call history
function viewCallHistory(lostPersonId) {
    $.ajax({
        url: SITE_URL + '/calling/get-call-history',
        type: 'GET',
        data: { lost_id: lostPersonId },
        beforeSend: function() {
            showLoader('#callHistoryModal .modal-content', 'historyLoader');
        },
        success: function(response) {
            hideLoader('historyLoader');
            if (response.success) {
                var html = '<div class="table-responsive">';
                html += '<table class="table table-bordered table-striped">';
                html += '<thead class="table-light"><tr>';
                html += '<th>Date</th>';
                html += '<th>Time</th>';
                html += '<th>Status</th>';
                html += '<th>Duration</th>';
                html += '<th>Notes</th>';
                html += '<th>Complainant Response</th>';
                html += '<th>Next Call Date</th>';
                html += '</tr></thead><tbody>';

                if (response.callHistory && response.callHistory.length > 0) {
                    response.callHistory.forEach(function(call) {
                        var statusBadge = '';
                        switch (call.call_status) {
                            case 'not_reachable':
                                statusBadge = '<span class="badge bg-danger">Not Reachable</span>';
                                break;
                            case 'not_picked_up':
                                statusBadge = '<span class="badge bg-warning">Not Picked Up</span>';
                                break;
                            case 'picked_up':
                                statusBadge = '<span class="badge bg-info">Picked Up</span>';
                                break;
                            case 'confirmed_found':
                                statusBadge = '<span class="badge bg-success">Confirmed Found</span>';
                                break;
                            default:
                                statusBadge = '<span class="badge bg-secondary">Unknown</span>';
                        }

                        html += '<tr>';
                        html += '<td>' + call.call_date + '</td>';
                        html += '<td>' + (call.call_time || 'N/A') + '</td>';
                        html += '<td>' + statusBadge + '</td>';
                        html += '<td>' + (call.call_duration || 'N/A') + '</td>';
                        html += '<td>' + (call.call_notes || 'N/A') + '</td>';
                        html += '<td>' + (call.complainant_response || 'N/A') + '</td>';
                        html += '<td>' + (call.next_call_date || 'N/A') + '</td>';
                        html += '</tr>';
                    });
                } else {
                    html += '<tr><td colspan="7" class="text-center text-muted">No call history found.</td></tr>';
                }

                html += '</tbody></table></div>';
                $('#callHistoryContent').html(html);
                $('#callHistoryModal').modal('show');
            } else {
                showError(response.message);
            }
        },
        error: function() {
            hideLoader('historyLoader');
            showError('An error occurred while loading call history.');
        }
    });
}

// Update status function
function updateStatus(lostPersonId) {
    $('#update_lost_id').val(lostPersonId);
    $('#updateStatusForm')[0].reset();
    $('#updateStatusForm').removeClass('was-validated');
    $('#updateReuniteInfo').remove();

    $('#updateStatusModal').modal('show');
}

// Save status update
function saveStatusUpdate() {
    var form = $('#updateStatusForm')[0];

    if (!form.checkValidity()) {
        form.reportValidity();
        $('#updateStatusForm').addClass('was-validated');
        return;
    }

    var formData = new FormData(form);
    formData.append('call_date', new Date().toISOString().split('T')[0]);
    formData.append('call_time', new Date().toTimeString().slice(0, 5));

    $.ajax({
        url: SITE_URL + '/calling/save-call-log',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function() {
            showLoader('#updateStatusModal .modal-content', 'updateStatusLoader');
        },
        success: function(response) {
            hideLoader('updateStatusLoader');
            if (response.success) {
                $('#updateStatusModal').modal('hide');
                callingGrid.ajax.reload();
                showSuccess(response.message);
            } else {
                showError(response.message);
            }
        },
        error: function() {
            hideLoader('updateStatusLoader');
            showError('An error occurred while updating status.');
        }
    });
}

// Helper functions for showing/hiding loaders
function showLoader(container, loaderId) {
    $(container).append('<div id="' + loaderId + '" class="loader-overlay"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
}

function hideLoader(loaderId) {
    $('#' + loaderId).remove();
}

function showSuccess(message) {
    toastr.success(message, 'Success!', {
        closeButton: true,
        progressBar: true,
        timeOut: 3000,
        extendedTimeOut: 1000,
        preventDuplicates: true
    });
}

function showError(message) {
    toastr.error(message, 'Error!', {
        closeButton: true,
        progressBar: true,
        timeOut: 5000,
        extendedTimeOut: 1000,
        preventDuplicates: true
    });
}

function showWarning(message) {
    toastr.warning(message, 'Warning!', {
        closeButton: true,
        progressBar: true,
        timeOut: 4000,
        extendedTimeOut: 1000,
        preventDuplicates: true
    });
}

function showInfo(message) {
    toastr.info(message, 'Info!', {
        closeButton: true,
        progressBar: true,
        timeOut: 3000,
        extendedTimeOut: 1000,
        preventDuplicates: true
    });
}

function showConfirm(message, callback) {
    if (confirm(message)) {
        if (typeof callback === 'function') {
            callback();
        }
    }
}