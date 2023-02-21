let GiftCardsTable = function() {
    let dataUri = '';
    let redeemUri = '';
    let userId = '';
    let giftCardImage = '';

    let setUri = function(dataUri) {
        this.dataUri =dataUri;
    };

    let setUserId = function(userId, giftCardImage) {
        this.userId = userId;
        this.giftCardImage = giftCardImage;
    }

    let setRedeemUri = function(redeemUri) {
        this.redeemUri = redeemUri;
    }

    let declare = function(){
        let notOrderColumns = [7];
        if (this.userId !== 0) {
            notOrderColumns.push(0);
        }

        let datatable =$('#giftcardsTable').DataTable({
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
                    d.userId = this.userId;
                }

            },
            columnDefs: [
                {"className": "dt-center", "targets": "_all"},
                { 'orderable': false, targets: notOrderColumns }
            ],
            columns: [
                { data: 'get_owner', render: function(data, type) {
                    if(this.userId === 0) {
                        return data['name'] ? data['name'] : data['email'];
                    }

                    return '<img src="'+this.giftCardImage+'" class="giftcard-miniature">'
                } },
                { data: 'amount', render: DataTable.render.number( '.', ',', 0, '$' ) },
                { data: 'status', render: function(data, type) {
                    return data.charAt(0).toUpperCase() + data.slice(1);
                } },
                { data: 'claim_link', render: function(data, type) {
                    return '<a href="'+data+'" target="_blank">'+data.substring(0,25)+'</a>';
                } },
                { data: 'egifter_id' },
                { data: 'pending', render: function(data, type) {
                    if(data !== 1) {
                        return "<a class='btn btn-success text-white' style='cursor: not-allowed; pointer-events: none'><i class='material-icons opacity-10'>check</i></a>";
                    }

                    return "<a class='btn btn-warning text-white'><i class='material-icons opacity-10'>cancel</i></a>"
                } },
                { data: 'created_at', render: DataTable.render.datetime() },
                { data: 'challenge_code' }
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

        $('#giftcardsTable tbody').on('click', 'a.btn', function() {
            var data = datatable.row( $(this).parents('tr') ).data();
            enableGiftCard(data);
        });



        let enableGiftCard = function(data) {
            swal({
                title: "Are you sure?",
                text: 'Are you sure that you want to enable a gift card with a value of $'+data['amount']+'?',
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
                .then((activate) => {
                    if (activate) {
                        let postData = {
                            _token: getCsrfToken(),
                            card: data['id'],
                            userId: this.userId !== 0 ? this.userId :  data['get_owner']['id']
                        };
                        $.post(this.redeemUri, postData, function(data) {
                            if(data['code'] === 200) {
                                $.toast({
                                    heading: 'Notification',
                                    text: data['message'],
                                    showHideTransition: 'slide',
                                    icon: 'success',
                                    position: 'bottom-right',
                                    stack: true,
                                    hideAfter: 10000
                                });
                                datatable.ajax.reload();
                            } else {
                                $.toast({
                                    heading: 'Notification',
                                    text: data['message'],
                                    showHideTransition: 'slide',
                                    icon: 'error',
                                    position: 'bottom-right',
                                    stack: true,
                                    hideAfter: 10000
                                });
                            }
                        });
                    };
                });
        }

    }

    return {
        init: function() {
            declare();
        },
        setUri: function(dataUri) {
            setUri(dataUri);
        },
        setUserId: function(userId, giftCardImage) {
            setUserId(userId, giftCardImage);
        },
        setRedeemUri: function(redeemUri) {
            setRedeemUri(redeemUri);
        }
    }
}();
