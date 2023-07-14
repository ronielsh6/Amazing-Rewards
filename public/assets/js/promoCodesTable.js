let PromoCodesTable = function () {

    let setUri = function (dataUri) {
        this.dataUri = dataUri;
    };

    let setDeleteUri = function (deleteUri) {
        this.deleteUri = deleteUri;
    }

    let setFiltersUri = function (filtersUri) {
        this.filtersUri = filtersUri;
    }


    let declare = function () {
        let datatable = $('#promoCodesTable').DataTable({
            language: {
                processing: "Loading data ...",
                emptyTable: "There are no records to show",
                paginate: {
                    previous: "<",
                    next: ">",
                    first: "<<",
                    last: ">>"
                }
            },
            ajax: {
                url: this.dataUri,
                data: function (d) {
                    d.username = $('#usernameInput').val();
                    d.relative = $('#relativeInput').val();
                    d.points = $('#pointsInput').val();
                }

            },
            columnDefs: [
                {
                    'targets': 0,
                    'searchable': false,
                    'orderable': false,
                    'className': 'dt-body-center',
                    'render': function (data) {
                        return '<input type="checkbox" id="chkUsers" name="chkUsers" value="' + $('<div/>').text(data).html() + '">';
                    }
                },
                {
                    target: 1,
                    visible: false,
                    searchable: false
                },
                {"className": "dt-center", "targets": "_all"},
                {"className": "dt-center", "targets": "updated_at", render: DataTable.render.datetime()},
                {orderable: false, targets: [0, 3]}
            ],
            columns: [
                {data: 'id'},
                {data: 'targets', render: function (){
                    return '';
                    }},
                {data: 'code'},
                {data: 'amount'},
                {
                    data: 'expiration_date', render: function (data, type) {
                        return moment(data).format('MM/DD/YYYY');
                    }
                },
                {
                    data: 'id', render: function () {
                        return "<a id='editPromoCode' style='margin-right: 10px' class='btn btn-success text-white' ><i class='material-icons opacity-10'>edit</i> </a><a id='deletePromoCode' class='btn btn-danger text-white'><i class='material-icons opacity-10'>delete</i></a>"
                    }
                }
            ],
            filter: false,
            paging: true,
            processing: true,
            serverSide: true,
            pagingType: 'full_numbers',
            ordering: true,
            lengthMenu: [[50, 100, 200, -1], [50, 100, 200, 'ALL']],
            DisplayLenght: 100,
        });

        $('#filterData').on('click', function () {
            datatable.ajax.reload();
        });

        let sendCallback = function (response) {
            if (response['code'] === 200) {
                $.toast({
                    heading: 'Notification',
                    text: response['message'],
                    showHideTransition: 'slide',
                    icon: 'success',
                    position: 'bottom-right',
                    stack: true,
                    hideAfter: 10000
                });
                $('#promoFormModal').modal('hide');
                datatable.ajax.reload();
            } else {
                $.toast({
                    heading: 'Notification',
                    text: response['message'],
                    showHideTransition: 'slide',
                    icon: 'danger',
                    position: 'bottom-right',
                    stack: true,
                    hideAfter: 10000
                });
            }
        }

        $("#promoFormModal").on('hide.bs.modal', function () {
            cleanForm();
        });

        let cleanForm = function () {
            $.datepicker._clearDate($('#exp_date'));
            $('#title').val('');
            $('#body').val('');
            $('#amount').val('');
            $('#custom_code').val('');
            $("#targets_all").prop('checked', true);
            $("#targets_spec").prop('checked', false);
            $("#targets_generated").prop('checked', true);
            $('#targets').selectpicker('deselectAll');
            document.getElementById('targets_container').hidden = true;
            document.getElementById('custom_code_container').hidden = true;
        }

        $('#example-select-all').on('click', function () {
            // Get all rows with search applied
            var rows = datatable.rows({'search': 'applied'}).nodes();
            // Check/uncheck checkboxes for all rows in the table
            $('input[type="checkbox"]', rows).prop('checked', this.checked);

            if (this.checked) {
                $('#blockUsers').show();
            } else {
                $('#blockUsers').hide();
            }
        });

        $("#targets_all").change(function() {
            if(this.checked) {
                $("#targets_spec").prop('checked', false);
                document.getElementById('targets_container').hidden = true;
            } else if (!$("#targets_spec").checked) {
                $("#targets_spec").prop('checked', true);
                document.getElementById('targets_container').hidden = false;
            }
        });

        $("#targets_spec").change(function() {
            if(this.checked) {
                $("#targets_all").prop('checked', false);
                document.getElementById('targets_container').hidden = false;
            } else if (!$("#targets_all").checked) {
                $("#targets_all").prop('checked', true);
                document.getElementById('targets_container').hidden = true;
            }
        });

        $("#targets_generated").change(function() {
            if(this.checked) {
                document.getElementById('custom_code_container').hidden = true;
            } else if (!$("#targets_all").checked) {
                document.getElementById('custom_code_container').hidden = false;
            }
        });

        $('#code-to-copy').on('click', function () {
            // Get all rows with search applied
            var rows = datatable.rows({'search': 'applied'}).nodes();
            // Check/uncheck checkboxes for all rows in the table
            $('input[type="checkbox"]', rows).prop('checked', this.checked);

            if (this.checked) {
                $('#blockUsers').show();
            } else {
                $('#blockUsers').hide();
            }
        });

        $('#usersTable tbody').on('change', 'input[type="checkbox"]', function () {
            let chkbs = $('#usersTable tbody input[type="checkbox"]:checked');
            if (chkbs.length > 0) {
                $('#blockUsers').show();
            } else {
                $('#blockUsers').hide();
            }
        });

        $('#sendMessage').on('click', function () {
            $('#messageModal').modal('show');
        });

        $('#blockUsers').on('click', function () {
            let items = [];
            let checked = $('#usersTable tbody input[type="checkbox"]:checked');
            checked.each(function (id, obj) {
                let data = datatable.row($(obj).parents('tr')).data();
                items.push(data);
            });
            deleteElement(items, true);
        });


        $('.create-promo-code').on('click', function () {
            let data = {
                _token: getCsrfToken(),
                code: $('#custom_code').val(),
                targets: $('#targets_spec').is(":checked") ? $('#targets').val() : "all",
                expiration_date: moment($('#exp_date').val()).format('YYYY-MM-DD'),
                amount: $('#amount').val()
            };

            if ($('#method').val() === 'POST') {
                $.post($('#createUri').val(), data, function (response) {
                    sendCallback(response);
                });
            } else {
                $.post($('#updateUri').val(), data, function (response) {
                    sendCallback(response);
                });
            }
        });

        $('#targets').selectpicker({
            // text for select all
            selectAllText: 'Select All',

            // text for deselect all
            deselectAllText: 'Deselect All',
            // auto reposition to fit screen
            dropupAuto: true,

            // live filter options
            liveSearch: true,
            liveSearchPlaceholder: null,
            liveSearchNormalize: false,
            liveSearchStyle: 'contains',

            // shows Select All & Deselect All
            actionsBox: true,
        }).filter('.with-ajax').ajaxSelectPicker({
            ajax: {
                url: this.filtersUri,
                data: function () {
                    var params = {
                        q: '{{{q}}}'
                    };
                    if (gModel.selectedGroup().hasOwnProperty('ContactGroupID')) {
                        params.GroupID = gModel.selectedGroup().ContactGroupID;
                    }
                    return params;
                }
            },
            locale: {
                emptyTitle: 'Search for contact...'
            },
            preprocessData: function (data) {
                var contacts = [];
                if (data.hasOwnProperty('Contacts')) {
                    var len = data.Contacts.length;
                    for (var i = 0; i < len; i++) {
                        var curr = data.Contacts[i];
                        contacts.push(
                            {
                                'value': curr.ContactID,
                                'text': curr.FirstName + ' ' + curr.LastName,
                                'data': {
                                    'icon': 'icon-person',
                                    'subtext': 'Internal'
                                },
                                'disabled': false
                            }
                        );
                    }
                }
                return contacts;
            },
            preserveSelected: false
        });

        $('#promoCodesTable tbody').on('click', 'a', function () {
            var data = datatable.row($(this).parents('tr')).data();
            let $class = this.classList;
            if (jQuery.inArray('btn-warning', $class) !== -1) {
                watchRedeem(data['id']);
            }
            if (jQuery.inArray('btn-danger', $class) !== -1) {
                deleteElement(data);
            }
            if (jQuery.inArray('btn-success', $class) !== -1) {
                // creation = false;
                $('#promo_id').val(data['id']);
                let method = $('#promoFormModal').find('input#method');
                $(method).val('PUT');
                edit(data);
            }
        });

        let edit = function (data) {
            const obj = JSON.parse(data['targets']);
            $('#targets').selectpicker('val', obj['data']);
            $('#amount').val(data['amount']);
            $('#exp_date').val(moment(data['expiration_date']).format('MM/DD/YYYY'));
            $('#promoFormModal').modal('show');
        };

        let deleteElement = function (data, array = false) {
            let message = 'Are you sure that you want to block';
            message += array ? ' those users?' : ' this user?';
            swal({
                title: "Are you sure?",
                text: message,
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
                .then((willDelete) => {
                    if (willDelete) {
                        if (array) {
                            data.forEach(item => {
                                sendDelete(item);
                            })
                        } else {
                            sendDelete(data);
                        }
                    }
                });
        }

        let sendDelete = function (data) {
            let postData = {
                _token: getCsrfToken(),
                id: data['id']
            }
            $.post(this.deleteUri, postData, function (data) {
                if (data['code'] === 200) {
                    datatable.ajax.reload();
                    $.toast({
                        heading: 'Notification',
                        text: 'User blocked successfully.',
                        showHideTransition: 'slide',
                        icon: 'success',
                        position: 'bottom-right',
                        stack: true,
                        hideAfter: 10000
                    });
                } else {
                    $.toast({
                        heading: 'Notification',
                        text: 'There was a problem.',
                        showHideTransition: 'slide',
                        icon: 'error',
                        position: 'bottom-right',
                        stack: true,
                        hideAfter: 10000
                    });
                }
            });
        }

        $('#exp_date').datepicker({
            onSelect: function (selectedDate) {
                $('#end_date').datepicker('option', 'minDate', selectedDate);
            }
        });

        let watchRedeem = function (id) {
            $('#user-id-redeem').val(id);
            $('#userRedeemForm').trigger('submit');
        }

        $('#createPromoCode').on('click', function () {
            $('#campaign_id').val(0);
            creation = true;
            let method = $('#promoFormModal').find('input#method');
            $(method).val('POST');
            $('#promoFormModal').modal('show');
        });
    }

    return {
        init: function () {
            declare();
        },
        setUri: function (dataUri) {
            setUri(dataUri);
        },
        setDeleteUri: function (deleteUri) {
            setDeleteUri(deleteUri);
        },
        setFiltersUri: function (filtersUri) {
            setFiltersUri(filtersUri);
        }
    }
}();
