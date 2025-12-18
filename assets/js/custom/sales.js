$(document).ready(function () {
    // List page
    if ($('#sales-grid').length) {
        var salesGrid = $('#sales-grid').DataTable({
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
                url: SITE_URL + "/sales",
                beforeSend: function () {
                    showLoader('.card-body', 'salesLoader');
                },
                complete: function () {
                    hideLoader('salesLoader');
                },
                data: function (d) {
                    d.keywords = oFilter.keywords;
                    d.payment_status = oFilter.payment_status;
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
                { data: "invoice_number", width: "15%", orderable: true },
                { data: "customer", width: "25%", orderable: true },
                { data: "payment_status", width: "10%", orderable: true },
                { data: "sale_date", width: "15%", orderable: true },
                { data: "final_amount", width: "12%", orderable: true, className: "text-end" },
                { data: "created_at", width: "15%", orderable: true },
                { data: "actions", width: "8%", orderable: false, className: "text-center" }
            ],
            language: {
                info: "Showing _START_ to _END_ of _TOTAL_ sales",
                infoEmpty: "0 sales",
                emptyTable: "No sales found.",
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
                salesGrid.ajax.reload();
            }
        });

        $('#filter-payment-status').on('change', function () {
            oFilter.payment_status = $(this).val();
            nRecordsFiltered = 0;
            salesGrid.ajax.reload();
        });

        $('#filter-reset').on('click', function () {
            $('#filter-keywords').val('');
            $('#filter-payment-status').val('');
            oFilter.keywords = '';
            oFilter.payment_status = '';
            nRecordsFiltered = 0;
            salesGrid.ajax.reload();
        });

        $('#btn-export-sales').on('click', function () {
            var params = {
                keywords: $('#filter-keywords').val().trim(),
                payment_status: $('#filter-payment-status').val()
            };
            var queryParts = [];
            $.each(params, function (key, value) {
                if (value !== null && value !== undefined && value !== '') {
                    queryParts.push(encodeURIComponent(key) + '=' + encodeURIComponent(value));
                }
            });
            var queryString = queryParts.length ? '?' + queryParts.join('&') : '';
            window.location = SITE_URL + "/sales/export" + queryString;
        });

        $(document).on("click", "#sales-grid td a[data-action='delete']", function (e) {
            e.preventDefault();
            var $link = $(this);
            var id = $link.data('id');
            var name = $link.data('name');
            bootbox.confirm({
                title: 'Delete sale?',
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
                            url: SITE_URL + "/sales/delete/" + id,
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
                                    salesGrid.ajax.reload();
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
    if ($('#sale-items-table').length) {
        function parseNum(v) {
            var n = parseFloat(v);
            return isNaN(n) ? 0 : n;
        }

        function clampPct(p) {
            p = parseNum(p);
            if (p < 0) p = 0;
            if (p > 100) p = 100;
            return p;
        }

        function initProductSelect($select) {
            if (!$select || !$select.length) return;
            if (typeof $.fn.select2 === 'undefined') return;
            if ($select.hasClass('select2-hidden-accessible')) return;

            $select.select2({
                width: '100%',
                placeholder: 'Select product (type at least 3 characters)',
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

        function initCustomerSelect($select) {
            if (!$select || !$select.length) return;
            if (typeof $.fn.select2 === 'undefined') return;
            if ($select.hasClass('select2-hidden-accessible')) return;

            $select.select2({
                width: '100%',
                placeholder: 'Select customer (type at least 3 characters)',
                allowClear: true,
                minimumInputLength: 3,
                ajax: {
                    url: SITE_URL + '/customers/search',
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

        function recalcRow($row) {
            var qty = parseNum($row.find('.item-qty').val());
            var price = parseNum($row.find('.item-price').val());
            var discPct = clampPct($row.find('.item-discount-percent').val());
            var taxPct = clampPct($row.find('.item-tax-percent').val());

            // keep normalized
            $row.find('.item-discount-percent').val(discPct);
            $row.find('.item-tax-percent').val(taxPct);

            var base = qty * price;
            var disc = base * (discPct / 100);
            var net = base - disc;
            if (net < 0) net = 0;
            var tax = net * (taxPct / 100);
            var total = net + tax;
            $row.find('.item-total').text(total.toFixed(2));
            return { net: net, tax: tax, total: total };
        }

        function recalcTotals() {
            var sub = 0;
            var taxTotal = 0;
            $('#sale-items-table tbody tr.sale-item-row').each(function () {
                var r = recalcRow($(this));
                sub += r.net;
                taxTotal += r.tax;
            });
            var discount = parseNum($('#discount_amount').val());
            var finalTotal = sub + taxTotal - discount;
            if (finalTotal < 0) finalTotal = 0;

            $('#sale-subtotal').text(sub.toFixed(2));
            $('#sale-tax-total').text(taxTotal.toFixed(2));
            $('#sale-final-total').text(finalTotal.toFixed(2));

            // Keep hidden field synced (server recomputes anyway)
            $('#tax_amount').val(taxTotal.toFixed(2));
        }

        function filterLocationOptions() {
            var type = ($('#location_type').val() || '').toString();
            var $loc = $('#location_id');
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
                '<tr class="sale-item-row">' +
                '  <td><select class="form-select item-product js-product-select" name="item_product_id[]"><option value=""></option></select></td>' +
                '  <td><input type="number" class="form-control item-qty" name="item_quantity[]" value="" step="0.01" min="0"></td>' +
                '  <td><input type="number" class="form-control item-price" name="item_unit_price[]" value="" step="0.01" min="0"></td>' +
                '  <td><input type="number" class="form-control item-discount-percent" name="item_discount_percent[]" value="" step="0.01" min="0" max="100" placeholder="0"></td>' +
                '  <td><input type="number" class="form-control item-tax-percent" name="item_tax_percent[]" value="" step="0.01" min="0" max="100" placeholder="0"></td>' +
                '  <td class="item-total text-end fw-semibold">0.00</td>' +
                '  <td class="text-center"><button type="button" class="btn btn-sm btn-soft-danger btn-remove-item" title="Remove"><i class="ri-delete-bin-line"></i></button></td>' +
                '</tr>';
            $('#sale-items-table tbody').append(rowHtml);
            initProductSelect($('#sale-items-table tbody tr.sale-item-row:last .js-product-select'));
            recalcTotals();
        }

        // Init select2
        initCustomerSelect($('.js-customer-select'));
        $('#sale-items-table .js-product-select').each(function () { initProductSelect($(this)); });

        // Init location filtering
        filterLocationOptions();
        $('#location_type').on('change', function () {
            filterLocationOptions();
        });

        $('#btn-add-item').on('click', function () {
            addRow();
        });

        $(document).on('click', '.btn-remove-item', function () {
            var $row = $(this).closest('tr');
            var $tbody = $('#sale-items-table tbody');
            if ($tbody.find('tr.sale-item-row').length > 1) {
                // destroy select2 to avoid leaks
                var $sel = $row.find('.js-product-select');
                if ($sel.hasClass('select2-hidden-accessible')) {
                    $sel.select2('destroy');
                }
                $row.remove();
            } else {
                var $select = $row.find('.js-product-select');
                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.val(null).trigger('change');
                } else {
                    $select.val('');
                }
                $row.find('.item-qty').val('');
                $row.find('.item-price').val('');
                $row.find('.item-discount-percent').val('');
                $row.find('.item-tax-percent').val('');
                $row.find('.item-total').text('0.00');
            }
            recalcTotals();
        });

        $(document).on('input', '.item-qty, .item-price, .item-discount-percent, .item-tax-percent', function () {
            recalcTotals();
        });

        $('#discount_amount').on('input', function () {
            recalcTotals();
        });

        recalcTotals();
    }
});


