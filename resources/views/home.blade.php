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
                        <input type="text" class="form-control form-custom" id="usernameInput" placeholder="Username">
                    </div>
                    <div class="row col-4">
                        <div class="col-4">
                            <select id="relativeInput" class="form-control form-custom" style="float: left">
                                <option value=">">Points Above</option>
                                <option value="<">Points Under</option>
                                <option value="<>">Points Between</option>
                            </select>
                        </div>
                        <div class="col-8">
                            <input type="number" class="form-control form-custom" id="pointsInput" placeholder="Points" style="float: left">
                        </div>
                    </div>
                    <div class="col-2">
                        <a class="btn btn-primary" id="filterData">Filter</a>
                    </div>
                </div> 
            </div>
            <div class="p-0 col-12">
              <table class="table align-items-center mb-0" id="usersTable">
                <thead>
                  <tr>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Name</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Email</th>
                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Points</th>
                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Last connection at</th>
                    <th class="text-secondary opacity-7"></th>
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
    <div class="position-fixed bottom-1 end-10 z-index-2">
      <div class="toast fade p-2 bg-white hide" role="alert" aria-live="assertive" id="successToast" aria-atomic="true">
        <div class="toast-header border-0">
          <i class="material-icons text-success me-2">
            check
          </i>
          <span class="me-auto font-weight-bold">Amazing Rewards</span>
          <small class="text-body">now</small>
          <i class="fas fa-times text-md ms-3 cursor-pointer" data-bs-dismiss="toast" aria-label="Close" aria-hidden="true"></i>
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
    <script src="{{ asset('assets/js/usersTable.js') }}"></script>
    <script>
        UsersTable.setUri('{{ route('getUsers') }}');
        UsersTable.setDeleteUri('{{ route('deleteUser') }}');
        // UsersTable.setUri('{{ route('getUsers') }}');
        UsersTable.init();
    </script>
@endsection
