$(document).ready(function () {
    // List page
    if ($('#transfers-grid').length) {
        var transfersGrid = $('#transfers-grid').DataTable({
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
                url: SITE_URL + "/stock-transfers",
                beforeSend: function () {
                    showLoader('.card-body', 'transfersLoader');
                },
                complete: function () {
                    hideLoader('transfersLoader');
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
                { data: "transfer_number", width: "15%", orderable: true },
                { data: "from_location", width: "20%", orderable: false },
                { data: "to_location", width: "20%", orderable: false },
                { data: "status", width: "12%", orderable: true },
                { data: "dispatch_date", width: "15%", orderable: true },
                { data: "created_at", width: "15%", orderable: true },
                { data: "actions", width: "8%", orderable: false, className: "text-center" }
            ],
            language: {
                info: "Showing _START_ to _END_ of _TOTAL_ transfers",
                infoEmpty: "0 transfers",
                emptyTable: "No transfers found.",
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
                transfersGrid.ajax.reload();
            }
        });

        $('#filter-status').on('change', function () {
            oFilter.status = $(this).val();
            nRecordsFiltered = 0;
            transfersGrid.ajax.reload();
        });

        $('#filter-reset').on('click', function () {
            $('#filter-keywords').val('');
            $('#filter-status').val('');
            oFilter.keywords = '';
            oFilter.status = '';
            nRecordsFiltered = 0;
            transfersGrid.ajax.reload();
        });

        $('#btn-export-transfers').on('click', function () {
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
            window.location = SITE_URL + "/stock-transfers/export" + queryString;
        });

        $(document).on("click", "#transfers-grid td a[data-action='delete']", function (e) {
            e.preventDefault();
            var $link = $(this);
            var id = $link.data('id');
            var name = $link.data('name');
            bootbox.confirm({
                title: 'Delete stock transfer?',
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
                            url: SITE_URL + "/stock-transfers/delete/" + id,
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
                                    transfersGrid.ajax.reload();
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
    if ($('#transfer-items-table').length) {
        function initQrSelect($select) {
            if (!$select || !$select.length) return;
            if (typeof $.fn.select2 === 'undefined') return;
            if ($select.hasClass('select2-hidden-accessible')) return;

            var fromLocationType = ($('#from_location_type').val() || '').toString();
            var fromLocationId = $('#from_location_id').val();

            $select.select2({
                width: '100%',
                placeholder: 'Select QR code (min 3 char)',
                allowClear: true,
                minimumInputLength: 3,
                ajax: {
                    url: SITE_URL + '/stock-transfers/search-qr',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            term: params.term,
                            from_location_type: fromLocationType,
                            from_location_id: fromLocationId
                        };
                    },
                    processResults: function (data) {
                        return data;
                    },
                    cache: true
                }
            });
        }

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
            var count = $('#transfer-items-table tbody tr.transfer-item-row').length;
            $('#total-items-count').text(count);
        }

        function filterLocationOptions($select, locationTypeId) {
            var type = ($(locationTypeId).val() || '').toString();
            var $loc = $select;
            $loc.find('option').each(function () {
                var $opt = $(this);
                var optType = ($opt.data('type') || '').toString();
                if ($opt.val() === '') {
                    $opt.prop('disabled', false).show();
                    return;
                }
                var show = optType === type;
                $opt.prop('disabled', !show);
                if (show) {
                    $opt.show();
                } else {
                    $opt.hide();
                }
            });
            // reset if current selection doesn't match
            var $selected = $loc.find('option:selected');
            if ($selected.length && $selected.val() !== '' && ($selected.data('type') || '').toString() !== type) {
                $loc.val('');
            }
        }

        function addRow() {
            var rowHtml = '' +
                '<tr class="transfer-item-row">' +
                '  <td><select class="form-select item-qr js-qr-select" name="item_qr_id[]"><option value=""></option></select><input type="hidden" class="item-source-stock-id" name="item_source_stock_id[]" value=""></td>' +
                '  <td><select class="form-select item-product js-product-select" name="item_product_id[]"><option value=""></option></select></td>' +
                '  <td><input type="number" class="form-control item-qty" name="item_quantity[]" value="1" step="0.01" min="0"></td>' +
                '  <td class="item-stock-info text-muted small">—</td>' +
                '  <td class="text-center"><button type="button" class="btn btn-sm btn-soft-danger btn-remove-item" title="Remove"><i class="ri-delete-bin-line"></i></button></td>' +
                '</tr>';
            $('#transfer-items-table tbody').append(rowHtml);
            var $lastRow = $('#transfer-items-table tbody tr.transfer-item-row:last');
            initQrSelect($lastRow.find('.js-qr-select'));
            initProductSelect($lastRow.find('.js-product-select'));
            updateTotalItemsCount();
        }

        // Init select2
        $('#transfer-items-table .js-qr-select').each(function () { initQrSelect($(this)); });
        $('#transfer-items-table .js-product-select').each(function () { initProductSelect($(this)); });

        // Init location filtering
        filterLocationOptions($('#from_location_id'), '#from_location_type');
        filterLocationOptions($('#to_location_id'), '#to_location_type');

        $('#from_location_type').on('change', function () {
            filterLocationOptions($('#from_location_id'), '#from_location_type');
            // Reinitialize QR selects when from location changes
            $('#transfer-items-table .js-qr-select').each(function () {
                var $sel = $(this);
                if ($sel.hasClass('select2-hidden-accessible')) {
                    $sel.select2('destroy');
                }
                initQrSelect($sel);
            });
        });

        $('#to_location_type').on('change', function () {
            filterLocationOptions($('#to_location_id'), '#to_location_type');
        });

        $('#btn-add-item').on('click', function () {
            addRow();
        });

        $(document).on('click', '.btn-remove-item', function () {
            var $row = $(this).closest('tr');
            var $tbody = $('#transfer-items-table tbody');
            if ($tbody.find('tr.transfer-item-row').length > 1) {
                // destroy select2 to avoid leaks
                var $qrSel = $row.find('.js-qr-select');
                if ($qrSel.hasClass('select2-hidden-accessible')) {
                    $qrSel.select2('destroy');
                }
                var $prodSel = $row.find('.js-product-select');
                if ($prodSel.hasClass('select2-hidden-accessible')) {
                    $prodSel.select2('destroy');
                }
                $row.remove();
            } else {
                var $qrSelect = $row.find('.js-qr-select');
                if ($qrSelect.hasClass('select2-hidden-accessible')) {
                    $qrSelect.val(null).trigger('change');
                } else {
                    $qrSelect.val('');
                }
                var $select = $row.find('.js-product-select');
                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.val(null).trigger('change');
                } else {
                    $select.val('');
                }
                $row.find('.item-qty').val('1');
                $row.find('.item-source-stock-id').val('');
                $row.find('.item-stock-info').text('—');
            }
            updateTotalItemsCount();
        });

        // When QR code changes, fetch details & update product and stock info
        $(document).on('change', '.js-qr-select', function () {
            var $select = $(this);
            var $row = $select.closest('tr');
            var qrId = $select.val();
            var fromLocationType = ($('#from_location_type').val() || '').toString();
            var fromLocationId = $('#from_location_id').val();

            if (!qrId) {
                // Clear product and stock info if QR cleared
                var $prodSelect = $row.find('.js-product-select');
                if ($prodSelect.hasClass('select2-hidden-accessible')) {
                    $prodSelect.val(null).trigger('change');
                } else {
                    $prodSelect.val('');
                }
                $row.find('.item-source-stock-id').val('');
                $row.find('.item-stock-info').text('—');
                return;
            }

            $.ajax({
                url: SITE_URL + '/stock-transfers/qr-info',
                dataType: 'json',
                data: {
                    qr_id: qrId,
                    from_location_type: fromLocationType,
                    from_location_id: fromLocationId
                },
                success: function (response) {
                    if (!response || !response.success || !response.data) {
                        return;
                    }
                    var d = response.data;
                    var $prodSelect = $row.find('.js-product-select');

                    // If stock is not available for this QR at the from location, show error and clear selection
                    if (d.hasOwnProperty('stock_available') && d.stock_available === false) {
                        if (typeof toastr !== 'undefined') {
                            var qtyMsg = (typeof d.stock_quantity !== 'undefined' && d.stock_quantity !== null)
                                ? ' (Available: ' + d.stock_quantity + ')'
                                : '';
                            toastr["error"]('Stock not available for this QR at from location' + qtyMsg);
                        }
                        // Clear QR, product and stock info
                        if ($select.hasClass('select2-hidden-accessible')) {
                            $select.val(null).trigger('change');
                        } else {
                            $select.val('');
                        }
                        if ($prodSelect.hasClass('select2-hidden-accessible')) {
                            $prodSelect.val(null).trigger('change');
                        } else {
                            $prodSelect.val('');
                        }
                        $row.find('.item-source-stock-id').val('');
                        $row.find('.item-stock-info').text('—');
                        return;
                    }

                    if (d.product_id) {
                        // Ensure select2 has the option and select it
                        var option = new Option(d.product_text, d.product_id, true, true);
                        $prodSelect.append(option).trigger('change');
                    }

                    // Update stock info and source stock ID
                    if (d.stock_available && typeof d.stock_quantity !== 'undefined' && d.stock_quantity !== null) {
                        $row.find('.item-stock-info').text('Available: ' + parseFloat(d.stock_quantity).toFixed(2));
                    } else {
                        $row.find('.item-stock-info').text('—');
                    }

                    if (d.source_stock_id) {
                        $row.find('.item-source-stock-id').val(d.source_stock_id);
                    }
                }
            });
        });

        updateTotalItemsCount();
    }
});

