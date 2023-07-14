<div class="row">
    <input type="hidden" id="createUri" value="{{ route('createPromoCode') }}">
    <input type="hidden" id="updateUri" value="{{ route('updatePromoCode') }}">
    <input type="hidden" id="method" value="POST">
    <input type="hidden" id="promo_id" name="promo_id" value="0">

    <div class="col-6">
        <div class="form-group">
            <label for="deep_link">Settings</label>
            <ul class="list-group">
                <li class="list-group-item border-0 px-0">
                    <div class="form-check form-switch ps-0">
                        <input class="form-check-input ms-auto" type="checkbox" id="targets_generated" checked>
                        <label class="form-check-label text-body ms-3 text-truncate w-80 mb-0" for="flexSwitchCheckDefault3">Generated Code</label>
                    </div>
                </li>
                <li class="list-group-item border-0 px-0">
                    <div class="form-check form-switch ps-0">
                        <input class="form-check-input ms-auto" type="checkbox" id="targets_all" checked>
                        <label class="form-check-label text-body ms-3 text-truncate w-80 mb-0" for="flexSwitchCheckDefault3">All Users</label>
                    </div>
                </li>
                <li class="list-group-item border-0 px-0">
                    <div class="form-check form-switch ps-0">
                        <input class="form-check-input ms-auto" type="checkbox" id="targets_spec">
                        <label class="form-check-label text-body ms-3 text-truncate w-80 mb-0" for="flexSwitchCheckDefault4">Specific Targets</label>
                    </div>
                </li>
            </ul>
        </div>
    </div>
    <div class="col-4">
        <div class="form-group">
            <label for="start_date">Expiration Date</label>
            <input class="form-control" type="text" name="exp_date" id="exp_date">
        </div>
    </div>


    <div hidden="hidden" id="custom_code_container" class="col-4">
        <div class="form-group">
            <label for="custom_code">Custom Code</label>
            <input placeholder="CODE" class="form-control" name="custom_code" id="custom_code">
        </div>
    </div>

    <div class="col-4">
        <div class="form-group">
            <label for="amount">Amount</label>
            <input placeholder="points" class="form-control" type="number" name="amount" id="amount">
        </div>
    </div>

    <div class="row">
        <div hidden = "hidden" class="col-6" id="targets_container">
            <div class="form-group">
                <label for="deep_link">Targets</label>
                <select multiple class="form-control selectpicker" name="targets" id="targets" data-live-search="true">
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" >{{ $user->email}}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

</div>
