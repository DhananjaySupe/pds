$(document).ready(function () {
    var usersGrid = $('#users-grid').DataTable({
        responsive: true,
        autoWidth: false,
        lengthChange: false,
        searching: false,
        processing: false,
        serverSide: true,
        order: [[5, 'desc']],
        pageLength: 50,
        stateSave: true,
        ajax: {
            type: "POST",
            url: SITE_URL + "/users",
            beforeSend: function () {
                showLoader('.card-body', 'usersLoader');
            },
            complete: function () {
                hideLoader('usersLoader');
            },
            data: function (d) {
                d.keywords = oFilter.keywords;
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
            { data: "name", width: "30%", orderable: true },
            { data: "type", width: "10%", orderable: true },
            { data: "email", width: "20%", orderable: true },
            { data: "phone", width: "15%", orderable: true },
            { data: "status", width: "10%", orderable: true },
            { data: "created_at", width: "15%", orderable: true },
            { data: "actions", width: "10%", orderable: false, className: "text-center" }
        ],
        language: {
            info: "Showing _START_ to _END_ of _TOTAL_ users",
            infoEmpty: "0 users",
            emptyTable: "No users found.",
            paginate: {
                first: '<i class="ri-arrow-left-s-fill"></i>',
                previous: '<i class="ri-arrow-left-s-line"></i>',
                next: '<i class="ri-arrow-right-s-line"></i>',
                last: '<i class="ri-arrow-right-s-fill"></i>'
            }
        },
        initComplete: function () {
            hideLoader();
        }
    });

    $('#filter-keywords').on('keydown', function (e) {
        if (e.keyCode === 13) {
            oFilter.keywords = $(this).val();
            nRecordsFiltered = 0;
            usersGrid.ajax.reload();
        }
    });

    $('#filter-status').on('change', function () {
        oFilter.status = $(this).val();
        nRecordsFiltered = 0;
        usersGrid.ajax.reload();
    });

    $('#filter-reset').on('click', function () {
        $('#filter-keywords').val('');
        $('#filter-status').val('');
        oFilter.keywords = '';
        oFilter.status = '';
        nRecordsFiltered = 0;
        usersGrid.ajax.reload();
    });

    $('#btn-export-users').on('click', function () {
        var params = {
            keywords: $('#filter-keywords').val().trim(),
            status: $('#filter-status').val()
        };
        var queryParts = [];
        $.each(params, function (key, value) {
            if (value !== null && value !== undefined && value !== '') {
                queryParts.push(encodeURIComponent(key) + '=' + encodeURIComponent(value));
            }
        });
        var queryString = queryParts.length ? '?' + queryParts.join('&') : '';
        window.location = SITE_URL + "/users/export" + queryString;
    });

    $(document).on("click", "#users-grid td a[data-action='delete']", function (e) {
        e.preventDefault();
        var $link = $(this);
        var id = $link.data('id');
        var name = $link.data('name');
        bootbox.confirm({
            title: 'Delete user?',
            message: 'Are you sure you want to delete <b>"' + name + '"</b>?',
            className: 'bootbox-delete',
            centerVertical: true,
            swapButtonOrder: true,
            buttons: {
                confirm: { label: 'Delete', className: 'btn-primary' },
                cancel: { label: 'Cancel', className: 'btn-outline-secondary' }
            },
            callback: function (result) {
                if (result) {
                    var button = $('.bootbox-delete button.bootbox-accept');
                    $.ajax({
                        type: "DELETE",
                        url: SITE_URL + "/users/delete/" + id,
                        dataType: 'json',
                        beforeSend: function () {
                            button.attr('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
                        },
                        complete: function () {
                            button.removeAttr('disabled').html('Delete');
                        },
                        success: function (response) {
                            if (response.success) {
                                nRecordsTotal = 0;
                                nRecordsFiltered = 0;
                                usersGrid.ajax.reload();
                                if (typeof response.message !== 'undefined') {
                                    toastr["success"](response.message);
                                }
                            } else if (typeof response.message !== 'undefined') {
                                toastr["error"](response.message);
                            }
                        }
                    });
                }
            }
        });
    });
});

