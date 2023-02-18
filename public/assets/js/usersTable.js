let UsersTable = function() {
    let dataUri = '';
    let deleteUri = '';
    let redeemUri = '';

    let setUri = function(dataUri) {
        this.dataUri =dataUri;
    };

    let setDeleteUri = function(deleteUri) {
        this.deleteUri = deleteUri;
    }

    let setRedeemUri = function(redeemUri) {
        this.redeemUri = redeemUri;
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
                {"className": "dt-center", "targets": "updated_at", render: DataTable.render.datetime()}
            ],
            columns: [
                { data: 'name' },
                { data: 'email' },
                { data: 'points' },
                { data: 'updated_at' },
                { data: 'id', render: function(data, type) {
                    return "<a class='btn btn-warning'><i class='material-icons opacity-10'>redeem</i></a><a class='btn btn-danger'><i class='material-icons opacity-10'>delete</i></a>"
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
            let response = confirm('Are you sure that you want to delete the user '+username);
            if(response) {
                postData = {
                    _token: getCsrfToken(),
                    user: data['id']
                }
                $.post(this.deleteUri, postData, function(data) {
                    debugger
                    if(data['code'] == 200) {
                        $('.toast-body').html(data['message']);
                        $('.toast').toast('show');
                        datatable.ajax.reload();
                    }
                })
            }
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
        setRedeemUri: function(redeemUri) {
            setRedeemUri(redeemUri);
        }
    }
}();