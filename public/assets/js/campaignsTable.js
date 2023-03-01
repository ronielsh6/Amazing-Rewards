let CampaignsTable = function() {
    let dataUri = '';

    let setUri = function(dataUri) {
        this.dataUri =dataUri;
    };

    let declare = function(){
        let datatable =$('#campaignsTable').DataTable({
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
                data: function(d){
                    d._token = getCsrfToken();
                }

            },
            columnDefs: [
                {"className": "dt-center", "targets": "_all"},
                { 'orderable': false, targets: [6] }
            ],
            columns: [
                {data: 'title'},
                {data: 'body'},
                { data: 'start_date', render: function(data, type) {
                    return moment(data).format('DD/MM/YYYY');
                } },
                { data: 'end_date', render: function(data, type) {
                        return moment(data).format('DD/MM/YYYY');
                } },
                { data: 'execution_time'},
                { data: 'frequency' , render: function(data, type) {
                    return data.charAt(0).toUpperCase() + data.slice(1);
                } },
                { data: 'parameters' },
                { data: 'executions', render: function (data, type) {
                    let length = data.length;
                    if (length > 0) {
                        return moment(data[length - 1].date).format('DD/MM/YYYY');
                    }
                    return 'No executions yet';
                } },
                { data: 'id', render: function (data, type) {
                    return '<a class="btn btn-warning text-white edit-campaign"><i class="material-icons opacity-10">edit</i></a><a class="btn btn-success text-white execute-on-demand"><i class="material-icons opacity-10">schedule</i></a>'
                } }
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
            },{
                id: 'users.app_version',
                label: 'User app version',
                type: 'double'
            }
        ];

        $('#builder').queryBuilder({
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

        $('#campaignsTable tbody').on('click', 'a', function() {
            var data = datatable.row( $(this).parents('tr') ).data();
            let $class = this.classList;
            if(jQuery.inArray('btn-warning', $class) !== -1){
                $('#campaign_id').val(data['id']);
                let method = $('#campaignFormModal').find('input#method');
                $(method).val('PUT');
                edit(data);
            }
        });

        let edit = function (data) {

            $('#builder').queryBuilder('setRulesFromSQL', data['parameters']);
            $('#execution_time').val(data['execution_time']);
            $('#start_date').val(moment(data['start_date']).format('MM/DD/YYYY'));
            $('#end_date').val(moment(data['end_date']).format('MM/DD/YYYY'));
            $('#frequency').val(data['frequency']);
            $('#title').val(data['title']);
            $('#body').val(data['body']);
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

        $('.submit-campaign').on('click', function () {

            let $parameters = $('#builder').queryBuilder('getSQL', $(this).data('stmt'), false);
            let data = {
                _token: getCsrfToken(),
                campaign_id: $('#campaign_id').val(),
                execution_time: $('#execution_time').val(),
                start_date: moment($('#start_date').val()).format('YYYY-MM-DD'),
                end_date: moment($('#end_date').val()).format('YYYY-MM-DD'),
                frequency: $('#frequency').val(),
                title: $('#title').val(),
                body: $('#body').val(),
                parameters: $parameters.sql
            };

            if ($('#method').val() === 'POST') {
                $.post($('#createUri').val(), data, function (response) {
                    sendCallback(response);
                });
            }else {
                $.post($('#updateUri').val(), data, function (response) {
                    sendCallback(response);
                });
            }

        });

        $('#createCampaign').on('click', function () {
            $('#campaign_id').val(0);
            let method = $('#campaignFormModal').find('input#method');
            $(method).val('POST');
            $('#campaignFormModal').modal('show');
        });

        $('.select2').select2({
            width: '20vw'
        });
    }

    return {
        init: function() {
            declare();
        },
        setUri: function(dataUri) {
            setUri(dataUri);
        }
    }
}();
