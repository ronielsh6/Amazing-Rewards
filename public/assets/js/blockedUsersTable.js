let BlockedUsersTable = function() {
    let dataUri = '';
    let deleteUri = '';
    let sendMessageUri = '';

    let setUri = function(dataUri) {
        this.dataUri =dataUri;
    };

    let setDeleteUri = function(deleteUri) {
        this.deleteUri = deleteUri;
    }

    let setSendMessageUri = function(sendMessageUri) {
        this.sendMessageUri = sendMessageUri;
    }

    let declare = function(){
        let datatable =$('#blockedUsersTable').DataTable({
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
                data: function(d){
                    d.username = $('#usernameInput').val();
                    d.relative = $('#relativeInput').val();
                    d.points = $('#pointsInput').val();
                }

            },
            columnDefs: [
                {"className": "dt-center", "targets": "_all"},
                {"className": "dt-center", "targets": "updated_at", render: DataTable.render.datetime()},
                { 'orderable': false, targets: [3] }
            ],
            columns: [
                { data: 'email' },
                { data: 'points' },
                { data: 'created_at', render: function (data, type) {
                    let currentDate = moment(new Date);
                    let createdAtDate = moment(data);
                    return (currentDate.diff(createdAtDate, 'days'))+ ' days';
                    } },
                { data: 'updated_at' },
                { data: 'id', render: function(data, type) {
                    return "<a class='btn btn-success text-white'><i class='material-icons opacity-10'>check</i></a>"
                }}
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

        $('#filterData').on('click', function() {
            datatable.ajax.reload();
        });

        $('#sendMessage').on('click', function() {
            $('#messageModal').modal('show');
        });

        $('.send-messages').on('click', function() {
            let uri = $('#messagesRoute').val();
            let title = $('#messageTitle').val();
            let deepLink = $('#deep_link').val();
            let body = $('#messageBody').val();
            let data = datatable.rows().data();
            let $customId = parseInt($('#customId').val());
            let ids = [];
            if ($customId === 0 ) {
                ids = jQuery.map(data, function (item) {
                    return item['id'];
                });
            } else {
                ids = [$customId];
            }
            let postData = {
                _token: getCsrfToken(),
                title: title,
                body: body,
                users: ids,
                deepLink: deepLink
            };
            $.post(uri, postData, function(data) {
                if(data['code'] === 200) {
                    $('#messageModal').modal('hide');
                    if(data['errors']) {
                        let message = 'There was a problem sending the message. Please check the logs.';
                        $.toast({
                            heading: 'Notification',
                            text: message,
                            showHideTransition: 'slide',
                            icon: 'warning',
                            position: 'bottom-right',
                            stack: true,
                            hideAfter: 10000
                        });
                        $('#user-message-form').trigger('reset');
                    } else {
                        $.toast({
                            heading: 'Notification',
                            text: 'Message send successfully.',
                            showHideTransition: 'slide',
                            icon: 'success',
                            position: 'bottom-right',
                            stack: true,
                            hideAfter: 10000
                        });
                        $('#user-message-form').trigger('reset');
                    }
                }
            });
            $('#customId').val(0);
        });

        $('#blockedUsersTable tbody').on('click', 'a', function() {
            var data = datatable.row( $(this).parents('tr') ).data();
            let $class = this.classList;
            if(jQuery.inArray('btn-success', $class) !== -1){
                deleteElement(data);
            }
        });

        let deleteElement = function(data) {
            swal({
                title: "Are you sure?",
                text: "Are you sure that you want to enable again this user?",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
                .then((willDelete) => {
                    if (willDelete) {
                        sendDelete(data);
                    }
                });
        }

        let sendDelete = function (data) {
                let postData = {
                    _token: getCsrfToken(),
                    user: data['id']
                }
                $.post(this.deleteUri, postData, function(data) {
                    if(data['code'] === 200) {
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

        let watchRedeem = function(id) {
            $('#user-id-redeem').val(id);
            $('#userRedeemForm').trigger('submit');
        }

    }

    return {
        init: function() {
            declare();
        },
        setUri: function(dataUri) {
            setUri(dataUri);
        },
        setDeleteUri: function(deleteUri) {
            setDeleteUri(deleteUri);
        },
        setSendMessageUri: function(sendMessageUri) {
            setSendMessageUri(sendMessageUri);
        }
    }
}();
