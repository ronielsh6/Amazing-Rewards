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

        let datatable =$('#active-giftcardsTable').DataTable({
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
                    d.active = true;
                }

            },
            columnDefs: [
                {"className": "dt-center", "targets": "_all"},
                { 'orderable': false, targets: notOrderColumns }
            ],
            columns: [
                { data: 'get_owner', render: function(data, type) {
                    if(this.userId === 0) {
                        return data['email'];
                    }

                    return '<img src="'+this.giftCardImage+'" class="giftcard-miniature">'
                } },
                { data: 'amount', render: DataTable.render.number( '.', ',', 0, '$' ) },
                { data: 'status', render: function(data, type) {
                    return data.charAt(0).toUpperCase() + data.slice(1);
                } },
                { data: 'claim_link', render: function(data, type) {
                    if (data != null){
                        return '<a href="'+data+'" target="_blank">'+data.substring(0,25)+'</a>';
                    }else{
                        return '<a href="'+data+'" target="_blank">PENDING</a>';
                    }

                } },
                { data: 'egifter_id' },
                { data: 'pending', render: function(data, type) {
                    return "<a class='btn btn-success text-white' style='cursor: not-allowed; pointer-events: none'><i class='material-icons opacity-10'>check</i></a>";
                } },
                { data: 'created_at', render: DataTable.render.datetime() },
                { data: 'challenge_code' }
            ],
            filter: false,
            paging: true,
            processing: true,
            serverSide: true,
            order: [[2, 'asc']],
            ordering: true,
            lengthMenu: [[50, 100, 200, -1], [50, 100, 200, 'ALL']],
            DisplayLenght: 50,
        });

        let pendingTable =$('#pending-giftcardsTable').DataTable({
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
                    d.active = false;
                }

            },
            columnDefs: [
                { 'targets': 0, 'searchable': false, 'orderable': false, 'className': 'dt-body-center', 'render': function (data) {
                        return '<input type="checkbox" id="chkUsers" name="chkUsers" value="' + $('<div/>').text(data).html() + '">';
                    }
                },
                {"className": "dt-center", "targets": "_all"},
                { 'orderable': false, targets: [6, 8] }
            ],
            columns: [
                { data: 'id'},
                { data: 'get_owner', render: function(data, type) {
                        if(this.userId === 0) {
                            return data['email'];
                        }

                        return '<img src="'+this.giftCardImage+'" class="giftcard-miniature">'
                    } },
                { data: 'amount', render: DataTable.render.number( '.', ',', 0, '$' ) },
                { data: 'status', render: function(data, type) {
                        return data.charAt(0).toUpperCase() + data.slice(1);
                    } },
                { data: 'claim_link', render: function(data, type) {
                        if (data != null){
                            return '<a href="'+data+'" target="_blank">'+data.substring(0,25)+'</a>';
                        }else{
                            return '<a href="'+data+'" target="_blank">PENDING</a>';
                        }

                    } },
                { data: 'egifter_id' },
                { data: 'pending', render: function(data, type) {
                        return "<a class='btn btn-warning text-white'><i class='material-icons opacity-10'>cancel</i></a>"
                    } },
                { data: 'created_at', render: DataTable.render.datetime() },
                { data: 'id', render: function(data, type) {
                        return "<a class='btn btn-danger text-white'><i class='material-icons opacity-10'>delete</i></a>"
                    } },
            ],
            filter: false,
            paging: true,
            processing: true,
            serverSide: true,
            order: [[2, 'asc']],
            ordering: true,
            lengthMenu: [[50, 100, 200, -1], [50, 100, 200, 'ALL']],
            DisplayLenght: 50,
        });


        $('#active-giftcards-tab').on('click', function (e) {
            datatable.ajax.reload();
        });

        $('#pending-giftcards-tab').on('click', function (e) {
            pendingTable.ajax.reload();
        });

        function deleteCard(data) {
            enableGiftCard(data, true);
        }

        $('#pending-giftcardsTable tbody').on('click', 'a.btn', function() {
            var data = pendingTable.row( $(this).parents('tr') ).data();
            let $class = this.classList;
            if (jQuery.inArray('btn-warning', $class) !== -1) {
                enableGiftCard([data]);
            } else {
                deleteCard([data]);
            }
        });

        $('#example-select-all').on('click', function(){
            // Get all rows with search applied
            var rows = pendingTable.rows({ 'search': 'applied' }).nodes();
            // Check/uncheck checkboxes for all rows in the table
            $('input[type="checkbox"]', rows).prop('checked', this.checked);

            if (this.checked) {
                $('#activateCards').show();
            } else {
                $('#activateCards').hide();
            }
        });

        $('#pending-giftcardsTable tbody').on('change', 'input[type="checkbox"]', function(){
            let chkbs = $('#pending-giftcardsTable tbody input[type="checkbox"]:checked');
            if (chkbs.length > 0) {
                $('#activateCards').show();
            } else {
                $('#activateCards').hide();
            }
        });

        $('#activateCards').on('click', function () {
            let items = [];
            let checked = $('#pending-giftcardsTable tbody input[type="checkbox"]:checked');
            checked.each(function (id, obj) {
                let data = pendingTable.row($(obj).parents('tr')).data();
                items.push(data);
            });
            enableGiftCard(items, true);
        });

        let enableGiftCard = function(data, deleteCard = false) {
            let message = 'Are you sure that you want to enable';
            message += data.length > 1 ? ' all these gift cards' : ' a gift card with a value of $'+data[0]['amount']+'?';
            let userIdLocal = this.userId;

            if (deleteCard) {
                message = 'Are you sure that you want to delete the card? This action is not reversible.';
            }

            swal({
                title: "Are you sure?",
                text: message,
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
                .then((activate) => {
                    if (activate) {
                        data.forEach(function (item) {
                            let postData = {
                                _token: getCsrfToken(),
                                card: item['id'],
                                userId: userIdLocal !== 0 ? userIdLocal :  item['get_owner']['id'],
                                deleteCard: deleteCard
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
                                    pendingTable.ajax.reload();
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
