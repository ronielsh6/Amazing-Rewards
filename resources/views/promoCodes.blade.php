@extends('layouts.app')

@section('aside-bar')
    @include('partials.aside-bar')
@endsection
@section('header')
    @include('partials.header')
@endsection

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card my-4">
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                            <h6 class="text-white text-capitalize ps-3">Promo Codes List</h6>
                        </div>
                    </div>
                    <div class="card-body px-0 pb-2">
                        <div class="row mr-5 ml-5">
                            <div class="row filters">
                                <div class="col-1">
                                    <a class="btn btn-primary text-white" id="createPromoCode">Create</a>
                                </div>
                            </div>
                        </div>
                        <div class="p-0 col-12">
                            <table class="table align-items-center mb-0" id="promoCodesTable">
                                <thead>
                                <tr>
                                    <th><input type="checkbox" name="select_all" value="1" id="example-select-all"></th>
                                    <th></th>
                                    <th>Code</th>
                                    <th>Amount</th>
                                    <th>Exp. Date</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <form class="d-none" method="GET" id="userRedeemForm" action="{{ route('showGiftCards') }}">
            @csrf
            <input type="hidden" name="user-id" id="user-id-redeem" value="0">
        </form>

        @include('partials.modal', [
        'modalId' => 'messageModal',
        'modalTitle' => 'Notification message',
        'modalButtonId' => 'send-messages',
        'modalButtonText' => 'Send messages',
        'body' => 'usersView.modalUsers',
        'color' => 'success'
        ] )
    </div>

    @include('partials.modal', [
    'modalId' => 'promoFormModal',
    'modalSize' => 'modal-lg',
    'modalTitle' => 'Promo Code',
    'modalButtonId' => 'create-promo-code',
    'modalButtonText' => 'Create',
    'body' => 'promoCodePartials.form',
    'color' => 'success'
    ] )
@endsection

@section('additional-scripts')
    <script src="{{ asset('assets/js/promoCodesTable.js') }}"></script>
    <script>
        PromoCodesTable.setUri('{{ route('promoCodesList') }}');
        PromoCodesTable.setDeleteUri('{{ route('promoCodeDelete') }}');
        PromoCodesTable.setFiltersUri('{{ route('filterPromoTarget') }}');
        PromoCodesTable.init();
    </script>
@endsection
