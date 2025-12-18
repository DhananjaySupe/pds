$(document).ready(function () {
    // List page DataTable (exists only on index page)
    if ($('#purchase-orders-grid').length) {
        var poGrid = $('#purchase-orders-grid').DataTable({
            responsive: true,
            autoWidth: false,
            lengthChange: false,
            searching: false,
            processing: false,
            serverSide: true,
            order: [[6, 'desc']],
            pageLength: 50,
            stateSave: true,
            ajax: {
                type: "POST",
                url: SITE_URL + "/purchase-orders",
                beforeSend: function () {
                    showLoader('.card-body', 'purchaseOrdersLoader');
                },
                complete: function () {
                    hideLoader('purchaseOrdersLoader');
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
                { data: "po_number", width: "15%", orderable: true },
                { data: "vendor", width: "20%", orderable: true },
                { data: "status", width: "10%", orderable: true },
                { data: "order_date", width: "12%", orderable: true },
                { data: "expected_delivery_date", width: "12%", orderable: true },
                { data: "final_amount", width: "10%", orderable: true, className: "text-end" },
                { data: "created_at", width: "12%", orderable: true },
                { data: "actions", width: "9%", orderable: false, className: "text-center" }
            ],
            language: {
                info: "Showing _START_ to _END_ of _TOTAL_ purchase orders",
                infoEmpty: "0 purchase orders",
                emptyTable: "No purchase orders found.",
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
                poGrid.ajax.reload();
            }
        });

        $('#filter-status').on('change', function () {
            oFilter.status = $(this).val();
            nRecordsFiltered = 0;
            poGrid.ajax.reload();
        });

        $('#filter-reset').on('click', function () {
            $('#filter-keywords').val('');
            $('#filter-status').val('');
            oFilter.keywords = '';
            oFilter.status = '';
            nRecordsFiltered = 0;
            poGrid.ajax.reload();
        });

        $('#btn-export-purchase-orders').on('click', function () {
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
            window.location = SITE_URL + "/purchase-orders/export" + queryString;
        });

        $(document).on("click", "#purchase-orders-grid td a[data-action='delete']", function (e) {
            e.preventDefault();
            var $link = $(this);
            var id = $link.data('id');
            var name = $link.data('name');
            bootbox.confirm({
                title: 'Delete purchase order?',
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
                            url: SITE_URL + "/purchase-orders/delete/" + id,
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
                                    poGrid.ajax.reload();
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

    // Details page - line items + totals
    if ($('#po-items-table').length) {
        function initProductSelect($select) {
            if (!$select || !$select.length) return;
            if (typeof $.fn.select2 === 'undefined') return;

            // Avoid double-init
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

        function parseNum(v) {
            var n = parseFloat(v);
            return isNaN(n) ? 0 : n;
        }

        function recalcRow($row) {
            var qty = parseNum($row.find('.item-qty').val());
            var price = parseNum($row.find('.item-price').val());
            var tax = parseNum($row.find('.item-tax').val());
            var discount = parseNum($row.find('.item-discount').val());

            var base = qty * price;
            var total = base + tax - discount;
            if (total < 0) total = 0;

            $row.find('.item-total').text(total.toFixed(2));
            return {
                base: base,
                tax: tax,
                discount: discount,
                total: total
            };
        }

        function recalcTotals() {
            var sub = 0;
            var taxTotal = 0;
            var itemDiscountTotal = 0;
            $('#po-items-table tbody tr.po-item-row').each(function () {
                var r = recalcRow($(this));
                sub += r.base;
                taxTotal += r.tax;
                itemDiscountTotal += r.discount;
            });

            var purchaseDiscount = parseNum($('#discount_amount').val());
            var finalTotal = sub + taxTotal - itemDiscountTotal - purchaseDiscount;
            if (finalTotal < 0) finalTotal = 0;

            $('#po-subtotal').text(sub.toFixed(2));
            $('#po-tax-total').text(taxTotal.toFixed(2));
            $('#po-item-discount-total').text(itemDiscountTotal.toFixed(2));
            $('#po-final-total').text(finalTotal.toFixed(2));

            // Keep hidden field synced (server recomputes anyway)
            $('#tax_amount').val(taxTotal.toFixed(2));
        }

        function addRow() {
            var rowHtml = '' +
                '<tr class="po-item-row">' +
                '  <td><select class="form-select item-product js-product-select" name="item_product_id[]"><option value=""></option></select></td>' +
                '  <td><input type="number" class="form-control item-qty" name="item_quantity[]" value="" step="0.01" min="0"></td>' +
                '  <td><input type="number" class="form-control item-price" name="item_unit_price[]" value="" step="0.01" min="0"></td>' +
                '  <td><input type="number" class="form-control item-tax" name="item_tax_amount[]" value="" step="0.01" min="0" placeholder="0.00"></td>' +
                '  <td><input type="number" class="form-control item-discount" name="item_discount_amount[]" value="" step="0.01" min="0" placeholder="0.00"></td>' +
                '  <td class="item-total text-end fw-semibold">0.00</td>' +
                '  <td class="text-center"><button type="button" class="btn btn-sm btn-soft-danger btn-remove-item" title="Remove"><i class="ri-delete-bin-line"></i></button></td>' +
                '</tr>';
            $('#po-items-table tbody').append(rowHtml);
            initProductSelect($('#po-items-table tbody tr.po-item-row:last .js-product-select'));
            recalcTotals();
        }

        $('#btn-add-item').on('click', function () {
            addRow();
        });

        $(document).on('click', '.btn-remove-item', function () {
            var $row = $(this).closest('tr');
            var $tbody = $('#po-items-table tbody');
            if ($tbody.find('tr.po-item-row').length > 1) {
                $row.remove();
            } else {
                // If it's the last row, just clear it
                var $select = $row.find('.item-product');
                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.val(null).trigger('change');
                } else {
                    $select.val('');
                }
                $row.find('.item-qty').val('');
                $row.find('.item-price').val('');
                $row.find('.item-tax').val('');
                $row.find('.item-discount').val('');
                $row.find('.item-total').text('0.00');
            }
            recalcTotals();
        });

        $(document).on('input', '.item-qty, .item-price, .item-tax, .item-discount', function () {
            recalcTotals();
        });

        $('#discount_amount').on('input', function () {
            recalcTotals();
        });

        // Init existing product selects (edit mode rows)
        $('#po-items-table .js-product-select').each(function () {
            initProductSelect($(this));
        });

        recalcTotals();
    }
});


