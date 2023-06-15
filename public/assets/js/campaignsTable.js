let CampaignsTable = function () {
    let dataUri = '';
    let creation = true;

    let setUri = function (dataUri) {
        this.dataUri = dataUri;
    };

    let declare = function () {
        let datatable = $('#campaignsTable').DataTable({
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
                method: 'POST',
                url: this.dataUri,
                data: function (d) {
                    d._token = getCsrfToken();
                }

            },
            columnDefs: [
                {"className": "dt-center", "targets": "_all"},
                {'orderable': false, targets: [6, 7, 8]}
            ],
            columns: [
                {data: 'title'},
                {
                    data: 'body', render: function (data, type) {
                        if (data.length > 22) {
                            return data.substring(0, 22) + '...';
                        }
                        return data;
                    }
                },
                {
                    data: 'start_date', render: function (data, type) {
                        return moment(data).format('MM/DD/YYYY');
                    }
                },
                {
                    data: 'end_date', render: function (data, type) {
                        return moment(data).format('MM/DD/YYYY');
                    }
                },
                {data: 'execution_time'},
                {
                    data: 'frequency', render: function (data, type) {
                        return data.charAt(0).toUpperCase() + data.slice(1);
                    }
                },
                {data: 'parameters', render: function (data, type) {
                    if (data.length > 22) {
                        return data.substring(0, 22) + '...';
                    }
                    return data;
                    }},
                {
                    data: 'executions', render: function (data, type) {
                        let length = data.length;
                        if (length > 0) {
                            return moment(data[length - 1].date).format('MM/DD/YYYY');
                        }
                        return 'No executions yet';
                    }
                },
                {
                    data: 'id', render: function (data, type) {
                        return '<a class="btn btn-warning text-white edit-campaign"><i class="material-icons opacity-10">edit</i></a><a class="btn btn-success text-white execute-on-demand"><i class="material-icons opacity-10">schedule</i></a>'
                    }
                }
            ],
            filter: false,
            paging: true,
            processing: true,
            serverSide: true,
            order: [[1, 'asc']],
            ordering: true,
            lengthMenu: [[50, 100, 200, -1], [50, 100, 200, 'ALL']],
            DisplayLenght: 50,
        });

        let queryBuilderFilters = [
            {
                id: 'users.lock_screen',
                label: 'User lock screen',
                type: 'boolean',
                default_value: 'true',
                input: 'radio',
                values: ['true', 'false']
            }, {
                id: 'users.app_version',
                label: 'User app version',
                type: 'double'
            }, {
                id: 'users.points',
                label: 'User points',
                type: 'integer'
            }, {
                id: 'users.status',
                label: 'Allow List',
                type: 'string',
                default_value: 'active',
                input: 'radio',
                values: ['active', 'blocked']
            }, {
                id: 'gift_card.pending',
                label: 'Active Gift Card',
                type: 'boolean',
                default_value: 'true',
                input: 'radio',
                values: ['true', 'false']
            }
        ];

        let builder = $('#builder');
        builder.queryBuilder({
            filters: queryBuilderFilters
        });

        $('#start_date').datepicker({
            onSelect: function (selectedDate) {
                $('#end_date').datepicker('option', 'minDate', selectedDate);
            }
        });

        $('#end_date').datepicker({
            onSelect: function (selectedDate) {
                $('#start_date').datepicker('option', 'maxDate', selectedDate);
            }
        });

        $('.timepicker').timepicker({
            interval: 30,
            zindex: 9999999,
            timeFormat: 'HH:mm'
        });

        $('#campaignsTable tbody').on('click', 'a', function () {
            var data = datatable.row($(this).parents('tr')).data();
            let $class = this.classList;
            if (jQuery.inArray('btn-warning', $class) !== -1) {
                creation = false;
                $('#campaign_id').val(data['id']);
                let method = $('#campaignFormModal').find('input#method');
                $(method).val('PUT');
                edit(data);
            }
            if (jQuery.inArray('btn-success', $class) !== -1) {
                execute({
                    _token: getCsrfToken(),
                    id: data['id']
                });
            }
        });

        let execute = function (data) {
            $.post(executeUri, data, function (response) {
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
                    $('#campaignFormModal').modal('hide');
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
                    $('#campaignFormModal').modal('hide');
                    datatable.ajax.reload();
                }
            });
        }

        $("#campaignFormModal").on('hide.bs.modal', function () {
            cleanForm();
        });

        $("#flexSwitchCheckEmail").change(function() {
            if(this.checked) {
                console.log("email checked");
            } else if (!$("#flexSwitchCheckPush").checked) {
                $("#flexSwitchCheckPush").prop('checked', true);
            }
        });

        $("#flexSwitchCheckPush").change(function() {
            if(this.checked) {
                console.log("push checked");
            } else if (!$("#flexSwitchCheckEmail").checked) {
                $("#flexSwitchCheckEmail").prop('checked', true);
            }
        });

        let cleanForm = function () {
            $.datepicker._clearDate($('#start_date'));
            $.datepicker._clearDate($('#end_date'));
            builder.queryBuilder('reset');
            $('#title').val('');
            $('#body').val('');
            $('#execution_time').val('');
            $('#impact-indicator').html('- users');
        }

        let edit = function (data) {
            builder.queryBuilder('setRulesFromSQL', data['parameters']);
            process();
            $('#execution_time').val(data['execution_time']);
            $('#start_date').val(moment(data['start_date']).format('MM/DD/YYYY'));
            $('#end_date').val(moment(data['end_date']).format('MM/DD/YYYY'));
            $('#frequency').val(data['frequency']);
            $('#deep_link').val(data['deep_link']);
            $('#title').val(data['title']);
            $('#body').val(data['body']);
            $('#flexSwitchCheckPush').val(data['is_push']);
            $('#flexSwitchCheckEmail').val(data['is_email']);
            $('.filepond').val(data['image']);
            $('#image_link').val(data['image_link']);
            $('#campaignFormModal').modal('show');
        };

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
                $('#campaignFormModal').modal('hide');
                cleanForm();
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

        builder.on('rulesChanged.queryBuilder', function (e, rule) {
            if (creation) {
                process();
            }
        });

        let process = function () {
            let valid = builder.queryBuilder('validate');
            if (valid) {
                let data = {
                    _token: getCsrfToken(),
                    query: builder.queryBuilder('getSQL', $(this).data('stmt'), false)['sql']
                }

                $.post(queryImpact, data, function (response) {
                    if (response['code'] === 200) {
                        $('#impact-indicator').html(response['total'] + ' users');
                    }
                });
            }
        }

        $('.submit-campaign').on('click', function () {
            let $parameters = builder.queryBuilder('getSQL', $(this).data('stmt'), false);
            let data = {
                _token: getCsrfToken(),
                campaign_id: $('#campaign_id').val(),
                execution_time: $('#execution_time').val(),
                start_date: moment($('#start_date').val()).format('YYYY-MM-DD'),
                end_date: moment($('#end_date').val()).format('YYYY-MM-DD'),
                frequency: $('#frequency').val(),
                deep_link: $('#deep_link').val(),
                title: $('#title').val(),
                body: $('#body').val(),
                is_push: $('#flexSwitchCheckPush').is(":checked") ? 1 : 0,
                is_email: $('#flexSwitchCheckEmail').is(":checked") ? 1 : 0,
                image: $('.filepond').val(),
                image_link: $('#image_link').val(),
                parameters: $parameters.sql
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

        $('#createCampaign').on('click', function () {
            $('#campaign_id').val(0);
            creation = true;
            let method = $('#campaignFormModal').find('input#method');
            $(method).val('POST');
            $('#campaignFormModal').modal('show');
        });

        $('.select2').select2({
            width: '20vw'
        });
        $('.frequency').select2({
            width: '12vw'
        });
    }

    return {
        init: function () {
            declare();
        },
        setUri: function (dataUri) {
            setUri(dataUri);
        }
    }
}();
