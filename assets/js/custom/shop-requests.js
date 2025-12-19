$(document).ready(function () {
    // List page
    if ($('#requests-grid').length) {
        var requestsGrid = $('#requests-grid').DataTable({
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
                url: SITE_URL + "/shop-requests",
                beforeSend: function () {
                    showLoader('.card-body', 'requestsLoader');
                },
                complete: function () {
                    hideLoader('requestsLoader');
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
                { data: "request_number", width: "15%", orderable: true },
                { data: "shop_name", width: "20%", orderable: false },
                { data: "godown_name", width: "20%", orderable: false },
                { data: "status", width: "12%", orderable: true },
                { data: "request_date", width: "15%", orderable: true },
                { data: "created_at", width: "15%", orderable: true },
                { data: "actions", width: "8%", orderable: false, className: "text-center" }
            ],
            language: {
                info: "Showing _START_ to _END_ of _TOTAL_ requests",
                infoEmpty: "0 requests",
                emptyTable: "No requests found.",
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
                requestsGrid.ajax.reload();
            }
        });

        $('#filter-status').on('change', function () {
            oFilter.status = $(this).val();
            nRecordsFiltered = 0;
            requestsGrid.ajax.reload();
        });

        $('#filter-reset').on('click', function () {
            $('#filter-keywords').val('');
            $('#filter-status').val('');
            oFilter.keywords = '';
            oFilter.status = '';
            nRecordsFiltered = 0;
            requestsGrid.ajax.reload();
        });

        $('#btn-export-requests').on('click', function () {
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
            window.location = SITE_URL + "/shop-requests/export" + queryString;
        });

        $(document).on("click", "#requests-grid td a[data-action='delete']", function (e) {
            e.preventDefault();
            var $link = $(this);
            var id = $link.data('id');
            var name = $link.data('name');
            bootbox.confirm({
                title: 'Delete shop request?',
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
                            url: SITE_URL + "/shop-requests/delete/" + id,
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
                                    requestsGrid.ajax.reload();
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
    }

    // Details page
    if ($('#request-items-table').length) {
        function initProductSelect($select) {
            if (!$select || !$select.length) return;
            if (typeof $.fn.select2 === 'undefined') return;
            if ($select.hasClass('select2-hidden-accessible')) return;

            $select.select2({
                width: '100%',
                placeholder: 'Select product (min 3 char)',
                allowClear: true,
                minimumInputLength: 3,
                ajax: {
                    url: SITE_URL + '/products/search',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return { term: params.term };
                    },
                    processResults: function (data) {
                        return data;
                    },
                    cache: true
                }
            });
        }

        function updateTotalItemsCount() {
            var count = $('#request-items-table tbody tr.request-item-row').length;
            $('#total-items-count').text(count);
        }

        function addRow() {
            var rowHtml = '' +
                '<tr class="request-item-row">' +
                '  <td><select class="form-select item-product js-product-select" name="item_product_id[]"><option value=""></option></select></td>' +
                '  <td><input type="number" class="form-control item-qty" name="item_quantity[]" value="1" step="0.01" min="0"></td>' +
                '  <td><input type="number" class="form-control item-priority" name="item_priority[]" value="0" step="1" min="0" max="10" placeholder="0"></td>' +
                '  <td><span class="text-muted">0.00</span></td>' +
                '  <td class="text-center"><button type="button" class="btn btn-sm btn-soft-danger btn-remove-item" title="Remove"><i class="ri-delete-bin-line"></i></button></td>' +
                '</tr>';
            $('#request-items-table tbody').append(rowHtml);
            var $lastRow = $('#request-items-table tbody tr.request-item-row:last');
            initProductSelect($lastRow.find('.js-product-select'));
            updateTotalItemsCount();
        }

        // Init select2
        $('#request-items-table .js-product-select').each(function () { initProductSelect($(this)); });

        $('#btn-add-item').on('click', function () {
            addRow();
        });

        $(document).on('click', '.btn-remove-item', function () {
            var $row = $(this).closest('tr');
            var $tbody = $('#request-items-table tbody');
            if ($tbody.find('tr.request-item-row').length > 1) {
                // destroy select2 to avoid leaks
                var $prodSel = $row.find('.js-product-select');
                if ($prodSel.hasClass('select2-hidden-accessible')) {
                    $prodSel.select2('destroy');
                }
                $row.remove();
            } else {
                var $select = $row.find('.js-product-select');
                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.val(null).trigger('change');
                } else {
                    $select.val('');
                }
                $row.find('.item-qty').val('1');
                $row.find('.item-priority').val('0');
            }
            updateTotalItemsCount();
        });

        updateTotalItemsCount();
    }
});

