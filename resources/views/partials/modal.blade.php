<div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog {{ $modalSize ?? '' }}" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">{{ $modalTitle }}</h5>
                <button type="button modal-dismiss" class="close" data-dismiss="modal" aria-label="Close">
                    <span class="modal-dismiss" aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                @include($body)
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-dismiss" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-{{ $color }} {{ $modalButtonId }}">{{ $modalButtonText }}</button>
            </div>
        </div>
    </div>
</div>
