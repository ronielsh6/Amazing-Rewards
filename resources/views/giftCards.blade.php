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
                <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                        <a class="nav-link active" id="active-giftcards-tab" data-bs-toggle="tab"
                           href="#enabled-giftcards" role="tab" aria-controls="nav-home" aria-selected="true">Pending
                            Gift Cards</a>
                        <a class="nav-link" id="pending-giftcards-tab" data-bs-toggle="tab" href="#pendign-giftcards"
                           role="tab" aria-controls="nav-profile" aria-selected="false">Active Gift Cards</a>
                    </div>
                </nav>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="enabled-giftcards" role="tabpanel"
                         aria-labelledby="home-tab">
                        <div class="card my-4">
                            <div class="card-body px-0 pb-2">
                                <div class="row mr-5 ml-5">
                                    <div class="row filters">
                                        <div class="col-2" @if(!is_null($userId)) style="display: none" @endif>
                                            <input type="text" class="form-control form-custom text-center" id="usernameInput"
                                                   placeholder="Email">
                                        </div>
                                        <div class="row col-4" @if(!is_null($userId)) style="display: none" @endif>
                                            <div class="col-4">
                                                <select id="relativeInput" class="form-control form-custom text-center"
                                                        style="float: left">
                                                    <option value=">">Amount Above</option>
                                                    <option value="<">Amount Under</option>
                                                    <option value="<>">Amount Between</option>
                                                </select>
                                            </div>
                                            <div class="col-8" @if(!is_null($userId)) style="display: none" @endif>
                                                <input type="number" class="form-control form-custom text-center"
                                                       id="pointsInput" placeholder="Amount" style="float: left">
                                            </div>
                                        </div>
                                        <div class="col-1" @if(!is_null($userId)) style="display: none" @endif>
                                            <a class="btn btn-primary text-white" id="filterData">Filter</a>
                                        </div>
                                        <div class="col-2">
                                            <a class="btn btn-success text-white" style="display: none"
                                               id="activateCards">Activate Cards</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-0 col-12">
                                    <table class="table align-items-center mb-0 w-100" id="pending-giftcardsTable">
                                        <thead>
                                        <tr>
                                            <th><input type="checkbox" name="select_all" value="1"
                                                       id="example-select-all"></th>
                                            <th>Gift Card</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Claim Link</th>
                                            <th>Egifter</th>
                                            <th>Pending</th>
                                            <th>Created At</th>
                                            <th></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="pendign-giftcards" role="tabpanel" aria-labelledby="profile-tab">
                        <div class="card my-4">
                            <div class="card-body px-0 pb-2">
                                <div class="p-0 col-12">
                                    <table class="table align-items-center mb-0 w-100" id="active-giftcardsTable">
                                        <thead>
                                        <tr>
                                            <th>Gift Card</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Claim Link</th>
                                            <th>Egifter</th>
                                            <th>Pending</th>
                                            <th>Created At</th>
                                            <th>Challenge Code</th>
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
            </div>
        </div>
        <form class="d-none" method="GET" id="userRedeemForm" action="{{ route('showGiftCards') }}">
            @csrf
            <input type="hidden" name="user-id" id="user-id-redeem" value="0">
        </form>
        <div class="position-fixed bottom-1 end-10 z-index-2">
            <div class="toast fade p-2 bg-white hide" role="alert" aria-live="assertive" id="successToast"
                 aria-atomic="true">
                <div class="toast-header border-0">
                    <i class="material-icons text-success me-2">
                        check
                    </i>
                    <span class="me-auto font-weight-bold">Amazing Rewards</span>
                    <small class="text-body">now</small>
                    <i class="fas fa-times text-md ms-3 cursor-pointer" data-bs-dismiss="toast" aria-label="Close"
                       aria-hidden="true"></i>
                </div>
                <hr class="horizontal dark m-0">
                <div class="toast-body">
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection

@section('additional-scripts')
    <script src="{{ asset('assets/js/giftcardsTable.js') }}"></script>
    <script>
        GiftCardsTable.setUri('{{ route('getGiftCards') }}');
        GiftCardsTable.setRedeemUri('{{ route('enableGiftcard') }}');
        GiftCardsTable.setUserId({{ $userId ?? 0}}, "{{ asset('assets/img/giftcard.png') }}");
        GiftCardsTable.init();
    </script>
@endsection
