// Add CSS for loader overlay
var loaderCSS = `
<style>
.loader-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.loader-overlay .spinner-border {
    width: 3rem;
    height: 3rem;
}

.btn:disabled {
    cursor: not-allowed;
    opacity: 0.6;
}
</style>
`;

// Inject CSS into head
$('head').append(loaderCSS);

var tvConfigurationGrid;
$(document).ready(function () {
    tvConfigurationGrid = $('#tv-configuration-grid').DataTable({
        responsive: true,
        autoWidth: true,
        lengthChange: false,
        searching: false,
        scrollY: "calc(100vh - 290px)",
        scrollX: "100%",
        scrollCollapse: false,
        processing: false,
        serverSide: true,
        sorting: [],
        order: [[5, 'desc']],
        paging: true,
        deferRender: true,
        pageLength: 50,
        stateSave: true,
        ajax: {
            type: "POST",
            url: SITE_URL + "/tv-configuration",
            beforeSend: function () {
                showLoader('.page-content', 'tvConfigurationLoader');
            },
            complete: function () {
                hideLoader('tvConfigurationLoader');
            },
            data: function (d) {
                d.keywords = oFilter.keywords;
                d.center_id = oFilter.center_id;
                d.configuration_type = oFilter.configuration_type;
                d.status = oFilter.status;
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
            { data: "center_name", width: "150px", orderable: false },
            { data: "configuration_type", width: "120px", orderable: false },
            { data: "priority_rules", width: "200px", orderable: false },
            { data: "status", width: "100px", orderable: false },
            { data: "created_by", width: "120px", orderable: false },
            { data: "created_at", width: "120px", orderable: false },
            { data: "actions", width: "150px", orderable: false, class: "text-center" },
        ],
        pagingType: "full_numbers",
        language: {
            info: "Showing _START_ to _END_ of _TOTAL_ records",
            infoEmpty: "0 records",
            emptyTable: "No data available.",
            paginate: {
                first: '<i class=" ri-arrow-left-s-fill"></i>',
                previous: '<i class=" ri-arrow-left-s-line"></i>',
                next: '<i class=" ri-arrow-right-s-line"></i>',
                last: '<i class=" ri-arrow-right-s-fill"></i>'
            }
        },
        createdRow: function (row, data, dataIndex) {
            if ('id' in data) {
                $(row).attr('data-id', data.id);
            }
        },
        initComplete: function (settings, json) {
            hideLoader();
        }
    });

    // Command bar clear filter
    $("#btn-clearfilter").on("click", function () {
        $('#filter-keywords').val('');
        $('#filter-center_id').val('').trigger('change');
        $('#filter-configuration_type').val('').trigger('change');
        $('#filter-status').val('').trigger('change');

        oFilter.keywords = '';
        oFilter.center_id = '';
        oFilter.configuration_type = '';
        oFilter.status = '';

        nRecordsTotal = 0;
        nRecordsFiltered = 0;
        tvConfigurationGrid.ajax.reload();
        refreshCommandbar();
    });

    // Keywords filter
    $('#filter-keywords').keydown(function (e) {
        var keycode = (e.keyCode ? e.keyCode : e.which);
        var val = $(this).val();
        if (keycode == 13) {
            oFilter.keywords = val;
            if (oFilter.keywords.length > 0) {
                $('.search-input a.clear').removeClass('d-none').show();
            } else {
                $('.search-input a.clear').addClass('d-none').hide();
            }
            nRecordsFiltered = 0;
            tvConfigurationGrid.ajax.reload();
        }
    });

    // Center filter
    $('#filter-center_id').on("change", function (e) {
        e.preventDefault();
        oFilter.center_id = $(this).val();
        nRecordsFiltered = 0;
        tvConfigurationGrid.ajax.reload();
    });

    // Configuration type filter
    $('#filter-configuration_type').on("change", function (e) {
        e.preventDefault();
        oFilter.configuration_type = $(this).val();
        nRecordsFiltered = 0;
        tvConfigurationGrid.ajax.reload();
    });

    // Status filter
    $('#filter-status').on("change", function (e) {
        e.preventDefault();
        oFilter.status = $(this).val();
        nRecordsFiltered = 0;
        tvConfigurationGrid.ajax.reload();
    });

    // Search input clear functionality
    $('.search-input a.clear').on("click", function (e) {
        e.preventDefault();
        $('#filter-keywords').val('');
        oFilter.keywords = '';
        $('.search-input a.clear').addClass('d-none').hide();
        nRecordsFiltered = 0;
        tvConfigurationGrid.ajax.reload();
    });
});

// Show success message
function showSuccessMessage(message) {
    Swal.fire({
        title: 'Success!',
        text: message,
        icon: 'success',
        confirmButtonText: 'OK'
    });
}

// Show error message
function showErrorMessage(message) {
    Swal.fire({
        title: 'Error!',
        text: message,
        icon: 'error',
        confirmButtonText: 'OK'
    });
}

// Refresh command bar
function refreshCommandbar() {
    // Add any additional command bar refresh logic here
}

// Refresh CSRF token
function refreshCSRFToken() {
    $.ajax({
        url: SITE_URL + '/csrf-refresh',
        type: 'GET',
        success: function(response) {
            if (response.csrf_token) {
                // Update all CSRF token inputs
                $('input[name="<?= csrf_token() ?>"]').val(response.csrf_token);
                $('meta[name="csrf-token"]').attr('content', response.csrf_token);
            }
        },
        error: function() {
            console.warn('Could not refresh CSRF token');
        }
    });
}

// Handle CSRF token errors
function handleCSRFError() {
    Swal.fire({
        title: 'Security Token Expired',
        text: 'Your security token has expired. The page will be refreshed to get a new token.',
        icon: 'warning',
        confirmButtonText: 'OK',
        allowOutsideClick: false
    }).then((result) => {
        window.location.reload();
    });
}

// Show loader
function showLoader(container, loaderId) {
    if (!loaderId) {
        loaderId = 'defaultLoader';
    }

    var loaderHtml = '<div id="' + loaderId + '" class="loader-overlay"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';

    if ($('#' + loaderId).length === 0) {
        $(container).append(loaderHtml);
    }

    $('#' + loaderId).show();
}

// Hide loader
function hideLoader(loaderId) {
    if (loaderId) {
        $('#' + loaderId).hide();
    } else {
        $('.loader-overlay').hide();
    }
}

// Utility function to format date
function formatDate(dateString) {
    if (!dateString) return 'N/A';

    var date = new Date(dateString);
    if (isNaN(date.getTime())) return 'N/A';

    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
}

// Utility function to format priority rules
function formatPriorityRules(rules) {
    if (!rules || rules === 'N/A') return 'N/A';

    try {
        var parsed = JSON.parse(rules);
        var formatted = [];

        if (parsed.time_priority) {
            formatted.push('Time: ' + parsed.time_priority + 'h');
        }
        if (parsed.age_group_priority) {
            formatted.push('Age Groups: ' + Object.keys(parsed.age_group_priority).length);
        }
        if (parsed.gender_priority && parsed.gender_priority !== 'none') {
            formatted.push('Gender: ' + parsed.gender_priority);
        }

        return formatted.length > 0 ? formatted.join(', ') : 'N/A';
    } catch (e) {
        return 'N/A';
    }
}

// Export configuration to PDF (placeholder function)
function exportToPDF(id) {
    // Implementation for PDF export
    alert('PDF export functionality will be implemented here');
}

// Print configuration (placeholder function)
function printConfiguration(id) {
    // Implementation for printing
    window.open(SITE_URL + '/tv-configuration/view/' + id, '_blank');
}

function deleteConfiguration(id) {
    bootbox.confirm({
        title: 'Delete TV Configuration?',
        message: 'Are you sure you want to delete this TV Configuration?',
        callback: function(result) {
            if (result) {
                $.ajax({
                    url: SITE_URL + '/tv-configuration/delete/' + id,
                    type: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        if (response.success) {
                           toastr.success(response.message);
                           tvConfigurationGrid.ajax.reload();
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        handleDeletionFailure(error);
                    }
                });
            }
        }
    });
}

function changeConfigurationStatus(id, currentStatus) {
    var action = currentStatus ? 'deactivate' : 'activate';
    var statusText = currentStatus ? 'deactivated' : 'activated';

    bootbox.confirm({
        title: 'Change Configuration Status?',
        message: 'Are you sure you want to ' + action + ' this TV Configuration?',
        callback: function(result) {
            if (result) {
                $.ajax({
                    url: SITE_URL + '/tv-configuration/change-status/' + id,
                    type: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        if (response.success) {
                           toastr.success(response.message);
                           tvConfigurationGrid.ajax.reload();
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        if (xhr.status === 403) {
                            handleCSRFError();
                        } else {
                            toastr.error('Error changing configuration status. Please try again.');
                        }
                    }
                });
            }
        }
    });
}

function handleDeletionFailure(error) {
    if (error === 'Forbidden') {
        handleCSRFError();
    } else {
        toastr.error('Error deleting TV Configuration. Please try again.');
    }
}


