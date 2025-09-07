var foundPeopleGrid;
$(document).ready(function () {
    foundPeopleGrid = $('#found-people-grid').DataTable({
        responsive: true, autoWidth: true, lengthChange: false, searching: false,
        scrollY: "calc(100vh - 290px)", scrollX: "100%",
        scrollCollapse: false, processing: false, serverSide: true, sorting: [], order: [[2, 'desc']],
        paging: true, deferRender: true, pageLength: 50, stateSave: true,
        ajax: {
            type: "POST",
            url: SITE_URL + "/found-people",
            beforeSend: function () { showLoader('.page-content', 'foundPeopleLoader'); },
			complete: function () { hideLoader('foundPeopleLoader'); },
            data: function (d) {
                d.keywords = oFilter.keywords;
                d.center_id = oFilter.center_id;
                d.gender = oFilter.gender;
                d.date = oFilter.date;
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
            { data: "image", width: "10px", orderable: false },
            { data: "param1", width: "120px", orderable: false },
            { data: "param2", width: "90px", orderable: false },
            { data: "param3", width: "90px", orderable: false },
            { data: "param4", width: "90px", orderable: false },
            { data: "param5", width: "100px", orderable: false },
            { data: "param6", width: "100px", orderable: false },
            { data: "actions", width: "50px", orderable: false, class: "text-center" },
		],
        pagingType: "full_numbers",
        language: { info: "Showing _START_ to _END_ of _TOTAL_ records", infoEmpty: "0 records", emptyTable: "No data available.", paginate: { first: '<i class=" ri-arrow-left-s-fill"></i>', previous: '<i class=" ri-arrow-left-s-line"></i>', next: '<i class=" ri-arrow-right-s-line"></i>', last: '<i class=" ri-arrow-right-s-fill"></i>' } },
        createdRow: function (row, data, dataIndex) { if ('id' in data) { $(row).attr('data-id', data.id); } },
        initComplete: function (settings, json) {
            hideLoader();
		}
	});
    $(document).on("click", "#found-people-grid td a[data-action]", function (e) {
        e.preventDefault();
        var rowindex = $(this).closest('tr').index();
        var data = foundPeopleGrid.rows(rowindex).data().toArray();
        var action = $(this).data('action');
        switch (action) {
            case 'delete':
			foundPeopleAction('delete', data[0].id, data[0].text);
			break;
		}
	});

    /** commandbar */
    $("#btn-clearfilter").on("click", function () {

        $('#filter-keywords').val('');
        $('#filter-center_id').val('').trigger('change');
        $('#filter-gender').val('').trigger('change');
        $('#filter-date').val('');
        oFilter.keywords = '';
        oFilter.center_id = '';
        oFilter.gender = '';
        oFilter.date = '';
        nRecordsTotal = 0;
        nRecordsFiltered = 0;
        foundPeopleGrid.ajax.reload();
        refreshCommandbar();
	});
    /** filters */
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
            foundPeopleGrid.ajax.reload();
		}
	});

	$("#filter-date").on("change", function (e) {
        e.preventDefault();
        oFilter.date = $(this).val();
        bids_nRecordsFiltered = 0;
        messages_nRecordsFiltered = 0;
        foundPeopleGrid.ajax.reload();
	});


	$('#filter-date').daterangepicker({
        autoUpdateInput: false,
        singleDatePicker: true,
        showDropdowns: false,
        autoApply: true,
        opens: "right",
        locale: {
            format: 'DD MMM YYYY'
		}
		}, function (start, end) {
        $("#filter-date").val(start.format('DD MMM YYYY')).change();
	});


    $("#filter-gender").on("change", function (e) {
        e.preventDefault();
        if($(this).val() != '') {
            oFilter.gender = $(this).val();
            nRecordsFiltered = 0;
            foundPeopleGrid.ajax.reload();
        }
    });

    $("#filter-center_id").on("change", function (e) {
        e.preventDefault();
        if($(this).val() != '') {
            oFilter.center_id = $(this).val();
            nRecordsFiltered = 0;
            foundPeopleGrid.ajax.reload();
        }
    });
});

function refreshCommandbar() {
	var count = foundPeopleGrid.rows().count();
    var selected =  foundPeopleGrid.rows({ selected: true }).count() ;
    if (count > 0 && selected > 0) {
        if ($("#bulkactionmenu").is(":hidden")) {
            $('#bulkactionmenu').removeClass('d-none').show();
		}
		} else {
        $('#bulkactionmenu').addClass('d-none').hide();
	}
}

function foundPeopleAction(action, id, text) {
    action = action || 'new'; id = id || 0; text = text || '';
    switch (true) {
        case action == "delete":
            bootbox.confirm({
                title: 'Delete found people?',
                message: 'Are you sure you want to delete <b>&quot;' + text + '&quot;</b>?',
                className: 'bootbox-delete',
                centerVertical: true,
                swapButtonOrder: true,
                buttons: { confirm: { label: 'Delete', className: 'btn-primary' }, cancel: { label: 'Cancel', className: 'btn-outline-secondary' } },
                callback: function (result) {
                    if (result) {
                        var params = $.param({ 'id': id });
                        var button = $('.bootbox-delete button.bootbox-accept');
                        $.ajax({
                            type: "DELETE", url: SITE_URL + "/found-people/delete?" + params, dataType: 'json',
                            beforeSend: function () { button.attr('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>'); },
                            complete: function () { button.removeAttr('disabled').html('Delete'); },
                            success: function (response) {
                                if (response.success) {
                                    nRecordsTotal = 0;
                                    nRecordsFiltered = 0;
                                    foundPeopleGrid.ajax.reload();
                                    if (typeof response.message != 'undefined') { toastr["success"](response.message); }
                                } else {
                                    if (typeof response.message != 'undefined') { toastr["error"](response.message); }
                                }
                            }
                        });
                    }
                }
            });
		break;
	}
}