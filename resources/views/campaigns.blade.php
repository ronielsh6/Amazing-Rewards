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
                            <h6 class="text-white text-capitalize ps-3">Campaigns table</h6>
                        </div>
                    </div>
                    <div class="card-body px-0 pb-2">
                        <div class="row mr-5 ml-5">
                            <div class="row filters">
                                <div class="col-4">
                                    <a class="btn btn-primary text-white" id="createCampaign">Create campaign</a>
                                </div>
                            </div>
                        </div>
                        <div class="p-0 col-12">
                            <table class="table align-items-center mb-0" id="campaignsTable">
                                <thead>
                                <tr>
                                    <th>Title message</th>
                                    <th>Body message</th>
                                    <th>Start date</th>
                                    <th>End Date</th>
                                    <th>Execution time</th>
                                    <th>Frequency</th>
                                    <th>Parameters</th>
                                    <th>Last Execution</th>
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

    @include('partials.modal', [
    'modalId' => 'campaignFormModal',
    'modalSize' => 'modal-lg',
    'modalTitle' => 'Campaign',
    'modalButtonId' => 'submit-campaign',
    'modalButtonText' => 'Submit campaign',
    'body' => 'campaignsPartials.form',
    'color' => 'success'
    ] )
@endsection

@section('additional-scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('assets/js/campaignsTable.js') }}"></script>
    <script>
        let executeUri = '{{ route('executeCampaign') }}';
        let queryImpact = '{{ route('queryImpact') }}'
        CampaignsTable.setUri('{{ route('campaignsList') }}');

        CampaignsTable.init();
    </script>
@endsection

@section('additional-styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection
