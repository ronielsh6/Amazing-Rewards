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
                            <h6 class="text-white text-capitalize ps-3">Blocked Users Logs List</h6>
                        </div>
                    </div>
                    <div class="card-body px-0 pb-2">
                        <div class="row mr-5 ml-5">
                            <div class="row filters">
                                <div class="col-2">
                                    <input type="text" class="form-control form-custom text-center" id="usernameInput"
                                           placeholder="Email">
                                </div>
                                <div class="col-1">
                                    <a class="btn btn-primary text-white" id="filterData">Filter</a>
                                </div>
                            </div>
                        </div>
                        <div class="p-0 col-12">
                            <table class="table align-items-center mb-0" id="blockedLogsTable">
                                <thead>
                                <tr>
                                    <th>User</th>
                                    <th>IP Address</th>
                                    <th>Country</th>
                                    <th>Reason</th>
                                    <th>Date and Time</th>
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
@endsection

@section('additional-scripts')
    <script src="{{ asset('assets/js/blockedLogsTable.js') }}"></script>
    <script>
        BlockedLogsTable.setUri('{{ route('blockedLogsList') }}');
        BlockedLogsTable.init();
    </script>
@endsection
