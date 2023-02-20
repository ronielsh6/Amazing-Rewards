let GeneralFunctions = function () {
    let declare = function () {
        $('.modal-dismiss').on('click', function() {
            let $modal = $(this).closest('div.modal');
            $modal.modal('hide');
        });
    }

    return {
        init: function () {
            declare();
        }
    }
}();
