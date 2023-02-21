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
              <h6 class="text-white text-capitalize ps-3">Users table</h6>
            </div>
          </div>
          <div class="card-body px-0 pb-2">
            <div class="row mr-5 ml-5">
                <div class="row filters">
                    <div class="col-2">
                        <input type="text" class="form-control form-custom text-center" id="usernameInput" placeholder="Username">
                    </div>
                    <div class="row col-4">
                        <div class="col-4">
                            <select id="relativeInput" class="form-control form-custom text-center" style="float: left">
                                <option value=">">Points Above</option>
                                <option value="<">Points Under</option>
                                <option value="<>">Points Between</option>
                            </select>
                        </div>
                        <div class="col-8">
                            <input type="number" class="form-control form-custom text-center" id="pointsInput" placeholder="Points" style="float: left">
                        </div>
                    </div>
                    <div class="col-1">
                        <a class="btn btn-primary text-white" id="filterData">Filter</a>
                    </div>
                    <div class="col-2">
                        <a class="btn btn-warning text-white" id="sendMessage">Send Message</a>
                    </div>
                </div>
            </div>
            <div class="p-0 col-12">
              <table class="table align-items-center mb-0" id="usersTable">
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Points</th>
                    <th>Last connection at</th>
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
</div>
@endsection

@section('additional-scripts')
    <script src="{{ asset('assets/js/usersTable.js') }}"></script>
    <script>
        UsersTable.setUri('{{ route('getUsers') }}');
        UsersTable.setDeleteUri('{{ route('deleteUser') }}');
        // UsersTable.setSendMessageUri('{{ route('sendMessages') }}');
        UsersTable.init();
    </script>
@endsection
