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
            ],
            columns: [
                { data: 'owner', render: function(data, type) {
                    return '<img src="'+this.giftCardImage+'" class="giftcard-miniature">'
                } },
                { data: 'amount', render: DataTable.render.number( '.', ',', 0, '$' ) },
                { data: 'status', render: function(data, type) {
                    return data.charAt(0).toUpperCase() + data.slice(1);
                } },
                { data: 'claim_link', render: function(data, type) {
                    return '<a href="'+data+'" target="_blank">'+data+'</a>';
                } },
                { data: 'egifter_id' },
                { data: 'pending', render: function(data, type) {
                    if(data !== 1) {
                        return "<a class='btn btn-success' style='cursor: not-allowed; pointer-events: none'><i class='material-icons opacity-10'>check</i></a>";
                    }

                    return "<a class='btn btn-warning'><i class='material-icons opacity-10'>cancel</i></a>"
                } },
                { data: 'created_at', render: DataTable.render.datetime() },
                { data: 'challenge_code' }
            ],
            filter: false,
            paging: true,
            processing: true,
            serverSide: true,
            ordering: true,
            lengthMenu: [[50, 100, 200, -1], [50, 100, 200, 'ALL']],
            DisplayLenght: 100,
        });

        $('#giftcardsTable tbody').on('click', 'a.btn', function() {
            var data = datatable.row( $(this).parents('tr') ).data();
            enableGiftCard(data);
        });

        let enableGiftCard = function(data) {
            debugger
            let response = confirm('Are you sure that you want to enable a gift card with a value of $'+data['amount']+'?');
            if(response) {
                postData = {
                    _token: getCsrfToken(),
                    card: data['id'],
                    userId: this.userId
                }
                $.post(this.redeemUri, postData, function(data) {
                    debugger
                    if(data['code'] == 200) {
                        $('.toast-body').html(data['message']);
                        $('.toast').toast('show');
                        datatable.ajax.reload();
                    }
                })
            }
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