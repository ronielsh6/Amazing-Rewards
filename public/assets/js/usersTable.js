let UsersTable = function() {
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
        let datatable =$('#usersTable').DataTable({
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
                { 'orderable': false, targets: [4] }
            ],
            columns: [
                { data: 'name' },
                { data: 'email' },
                { data: 'points' },
                { data: 'updated_at' },
                { data: 'id', render: function(data, type) {
                    return "<a class='btn btn-warning text-white'><i class='material-icons opacity-10'>redeem</i></a><a class='btn btn-danger text-white'><i class='material-icons opacity-10'>delete</i></a>"
                }}
            ],
            filter: false,
            paging: true,
            processing: true,
            serverSide: true,
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
            debugger
            let $ueri = this.sendMessageUri;
            let title = $('#messageTitle').val();
            let body = $('#messageBody').val();
            let postData = {
                _token: getCsrfToken(),
                title: title,
                body: body
            };
            $.post(this.sendMessageUri, postData, function(data) {
                if(data['code'] === 200) {
                    $('.toast-body').html(data['message']);
                    $('.toast').toast('show');
                    datatable.ajax.reload();
                }
            });
        });

        $('#usersTable tbody').on('click', 'a', function() {
            var data = datatable.row( $(this).parents('tr') ).data();
            let $class = this.classList;
            if(jQuery.inArray('btn-warning', $class) !== -1){
                watchRedeem(data['id']);
            }else {
                deleteElement(data);
            }
        });

        let deleteElement = function(data) {
            let username = data['name'] !== '' ? data['name'] : data['email'];
            ezBSAlert({
                type: "confirm",
                messageText: "Are you sure you want to delete the user "+username,
                alertType: "danger"
            }).done(function (e) {
                if (e) {
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
                        $('.toast-body').html(data['message']);
                        $('.toast').toast('show');
                        datatable.ajax.reload();
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
