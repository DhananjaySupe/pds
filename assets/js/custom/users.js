$(document).ready(function() {
    // Initialize DataTable
    var table = $('#usersTable').DataTable({
        responsive: true, autoWidth: true, lengthChange: false, searching: false,
        scrollY: "calc(100vh - 290px)", scrollX: "100%",
        scrollCollapse: false, processing: false, serverSide: true, sorting: [], order: [[2, 'desc']],
        paging: true, deferRender: true, pageLength: 50, stateSave: true,
        ajax: {
            url: window.location.href,
            type: 'POST',
            beforeSend: function () { showLoader('.page-content', 'usersLoader'); },
			complete: function () { hideLoader('usersLoader'); },
            data: function(d) {
                d.keywords = $('.search').val();
                d.role = $('#filterRole').val();
                d.status = $('#filterStatus').val();
                d.email = $('#filterEmail').val();
                d.phone = $('#filterPhone').val();
                d.daterange = $('#filterDate').val();
            }
        },
        columns: [
            { data: 'id', width: "10px", orderable: false },
            { data: 'image', width: "10px", orderable: false },
            { data: 'param1', width: "120px", orderable: false },
            { data: 'param2', width: "90px", orderable: false },
            { data: 'param3', width: "90px", orderable: false },
            { data: 'param4', width: "90px", orderable: false },
            { data: 'actions', width: "50px", orderable: false, class: "text-center" }
        ],
        pagingType: "full_numbers",
        language: { info: "Showing _START_ to _END_ of _TOTAL_ records", infoEmpty: "0 records", emptyTable: "No data available.", paginate: { first: '<i class=" ri-arrow-left-s-fill"></i>', previous: '<i class=" ri-arrow-left-s-line"></i>', next: '<i class=" ri-arrow-right-s-line"></i>', last: '<i class=" ri-arrow-right-s-fill"></i>' } },
        createdRow: function (row, data, dataIndex) { if ('id' in data) { $(row).attr('data-id', data.id); } },
        initComplete: function (settings, json) {
            hideLoader();
		}
    });

    // Search functionality
    $('.search').on('keyup', function() {
        table.draw();
    });

        // Reset filters functionality
    $('#btnFilter').on('click', function() {
        // Clear all filter inputs
        $('.search').val('');
        $('#filterRole').val('');
        $('#filterStatus').val('');
        $('#filterEmail').val('');
        $('#filterPhone').val('');
        $('#filterDate').val('');

        // Redraw table with cleared filters
        table.draw();

        // Show success message
        showToast('success', 'Filters have been reset successfully.');

        // Update filter button appearance
        updateFilterButtonStatus();
    });

    // Function to update filter button status
    function updateFilterButtonStatus() {
        var hasFilters = $('.search').val() ||
                        $('#filterRole').val() ||
                        $('#filterStatus').val() ||
                        $('#filterEmail').val() ||
                        $('#filterPhone').val() ||
                        $('#filterDate').val();

        if (hasFilters) {
            $('#btnFilter').removeClass('btn-warning').addClass('btn-danger').html('<i class="ri-refresh-line"></i>');
        } else {
            $('#btnFilter').removeClass('btn-danger').addClass('btn-warning').html('<i class="ri-refresh-line"></i>');
        }
    }

    // Check filter status on input changes
    $('.search, #filterRole, #filterStatus, #filterEmail, #filterPhone, #filterDate').on('input change', function() {
        updateFilterButtonStatus();
    });

    // Enter key on filter inputs
    $('#filterEmail, #filterPhone').on('keypress', function(e) {
        if (e.which == 13) {
            table.draw();
        }
    });

    // Select change events
    $('#filterRole, #filterStatus').on('change', function() {
        table.draw();
    });

    // Initialize date range picker
    $(function() {

        // Set default range to last 3 months
        var start = moment().subtract(3, 'months');
        var end = moment();
        function cd(start, end) {
            $('#filterDate').val(start.format('DD MMM YYYY') + ' - ' + end.format('DD MMM YYYY'));
        }
        function cb(start, end) {
            $('#filterDate').val(start.format('DD MMM YYYY') + ' - ' + end.format('DD MMM YYYY'));

            //alert(start.format('YYYY-MM-DD'));
            //alert(end.format('YYYY-MM-DD'));
            //Ajax call here\
            table.draw();
        }

        $('#filterDate').daterangepicker({
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
                'Last 3 Months': [moment().subtract(3, 'months'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'This Year': [moment().startOf('year'), moment().endOf('year')],
                'Last Year': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')]
            }
        }, cb,cd);

        cd(start, end);
        });

    // Profile photo upload
    $('#profile-img-file-input').on('change', function() {
        var file = this.files[0];
        if (file) {
            var formData = new FormData();
            formData.append('profilePhoto', file);
            formData.append('id', $('input[name="id"]').val());

            $.ajax({
                url: SITE_URL + '/users/save-photo',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        if (response.images && response.images.length > 0) {
                            var image = response.images[0];
                            $('#profileImage').attr('src', image.thumb);
                            $('#profilePhotoInput').val(image.filename);
                        }
                        showToast('success', response.message);
                    } else {
                        showToast('error', response.message);
                    }
                },
                error: function() {
                    showToast('error', 'Error uploading photo. Please try again.');
                }
            });
        }
    });

    // Form validation and submission
    $('#userForm').on('submit', function(e) {
        e.preventDefault();

        // Basic validation
        var password = $('#password').val();
        var confirmPassword = $('#confirm_password').val();
        var mode = $('input[name="id"]').val() == '0' ? 'new' : 'edit';

        if (mode == 'new' && password !== confirmPassword) {
            showToast('error', 'Password and confirm password do not match.');
            return false;
        }

        if (mode == 'edit' && password && password !== confirmPassword) {
            showToast('error', 'Password and confirm password do not match.');
            return false;
        }

        // Submit form
        var formData = new FormData(this);

        $.ajax({
            url: window.location.href,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showToast('success', response.message);
                    setTimeout(function() {
                        window.location.href = SITE_URL + '/users';
                    }, 1500);
                } else {
                    showToast('error', response.message);
                }
            },
            error: function() {
                showToast('error', 'Error saving user. Please try again.');
            }
        });
    });

    // Reset password functionality
    window.resetPassword = function(userId) {
        $('#resetPasswordModal').modal('show');
        $('#confirmResetPassword').off('click').on('click', function() {
            $.ajax({
                url: SITE_URL + '/users/reset-password',
                type: 'POST',
                data: { id: userId },
                success: function(response) {
                    if (response.success) {
                        $('#newPasswordDisplay').show();
                        $('#newPasswordText').text(response.response.new_password);
                        showToast('success', response.message);
                    } else {
                        showToast('error', response.message);
                    }
                },
                error: function() {
                    showToast('error', 'Error resetting password. Please try again.');
                }
            });
        });
    };

    // Delete user functionality
    window.deleteRecord = function(userId) {
        $('#deleteModal').modal('show');
        $('#confirmDelete').off('click').on('click', function() {
            $.ajax({
                url: SITE_URL + '/users/delete',
                type: 'POST',
                data: { id: userId },
                success: function(response) {
                    if (response.success) {
                        $('#deleteModal').modal('hide');
                        showToast('success', response.message);
                        table.draw();
                    } else {
                        showToast('error', response.message);
                    }
                },
                error: function() {
                    showToast('error', 'Error deleting user. Please try again.');
                }
            });
        });
    };

    // Logout user functionality
    window.logoutUser = function(userId) {
        if (confirm('Are you sure you want to logout this user? This will invalidate their current session.')) {
            $.ajax({
                url: SITE_URL + '/users/logout-user',
                type: 'POST',
                data: { id: userId },
                success: function(response) {
                    if (response.success) {
                        showToast('success', response.message);
                        table.draw();
                    } else {
                        showToast('error', response.message);
                    }
                },
                error: function() {
                    showToast('error', 'Error logging out user. Please try again.');
                }
            });
        }
    };

    // Export users to Excel functionality
    $('#btnExport').on('click', function() {
        var $btn = $(this);
        var originalText = $btn.html();

        // Show loading state
        $btn.prop('disabled', true).html('<i class="ri-loader-4-line fa-spin"></i>');

        // Get current filter values
        var filters = {
            keywords: $('.search').val(),
            role: $('#filterRole').val(),
            status: $('#filterStatus').val(),
            email: $('#filterEmail').val(),
            phone: $('#filterPhone').val(),
            date: $('#filterDate').val()
        };

        // Create form and submit for download
        var form = $('<form>', {
            'method': 'POST',
            'action': SITE_URL + '/users/export-excel'
        });

        // Add filter parameters to form
        $.each(filters, function(key, value) {
            if (value) {
                form.append($('<input>', {
                    'type': 'hidden',
                    'name': key,
                    'value': value
                }));
            }
        });

        // Add to body, submit, and remove
        $('body').append(form);
        form.submit();
        form.remove();

        // Reset button after a short delay
        setTimeout(function() {
            $btn.prop('disabled', false).html(originalText);
        }, 2000);
    });

    // Toast notification function
    function showToast(type, message) {
        var toastClass = type === 'success' ? 'bg-success' : 'bg-danger';
        var toastHtml = `
            <div class="toast align-items-center text-white ${toastClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;

        $('.toast-container').append(toastHtml);
        $('.toast').toast('show');

        // Remove toast after it's hidden
        $('.toast').on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Phone number formatting
    $('#phone').on('input', function() {
        var value = $(this).val().replace(/\D/g, '');
        if (value.length > 10) {
            value = value.substring(0, 10);
        }
        $(this).val(value);
    });

    // Email validation
    $('#email').on('blur', function() {
        var email = $(this).val();
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (email && !emailRegex.test(email)) {
            $(this).addClass('is-invalid');
            if (!$(this).next('.invalid-feedback').length) {
                $(this).after('<div class="invalid-feedback">Please enter a valid email address.</div>');
            }
        } else {
            $(this).removeClass('is-invalid');
            $(this).next('.invalid-feedback').remove();
        }
    });

    // Password strength indicator
    $('#password').on('input', function() {
        var password = $(this).val();
        var strength = 0;

        if (password.length >= 8) strength++;
        if (password.match(/[a-z]/)) strength++;
        if (password.match(/[A-Z]/)) strength++;
        if (password.match(/[0-9]/)) strength++;
        if (password.match(/[^a-zA-Z0-9]/)) strength++;

        var strengthText = '';
        var strengthClass = '';

        if (strength < 2) {
            strengthText = 'Weak';
            strengthClass = 'text-danger';
        } else if (strength < 4) {
            strengthText = 'Medium';
            strengthClass = 'text-warning';
        } else {
            strengthText = 'Strong';
            strengthClass = 'text-success';
        }

        if (password) {
            if (!$(this).next('.password-strength').length) {
                $(this).after('<small class="password-strength ' + strengthClass + '">Password strength: ' + strengthText + '</small>');
            } else {
                $(this).next('.password-strength').removeClass('text-danger text-warning text-success').addClass(strengthClass).text('Password strength: ' + strengthText);
            }
        } else {
            $(this).next('.password-strength').remove();
        }
    });
});