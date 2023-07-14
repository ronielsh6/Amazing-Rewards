<div class="row">
    <input type="hidden" id="createUri" value="{{ route('createPromoCode') }}">
    <input type="hidden" id="updateUri" value="{{ route('updatePromoCode') }}">
    <input type="hidden" id="method" value="POST">
    <input type="hidden" id="promo_id" name="promo_id" value="0">

    <div class="col-6">
        <div class="form-group">
            <label for="deep_link">Targets</label>
            <select multiple class="form-control selectpicker" name="targets" id="targets" data-live-search="true">
                @foreach($users as $user)
                    <option value="{{ $user->id }}" >{{ $user->email}}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-4">
        <div class="form-group">
            <label for="start_date">Expiration Date</label>
            <input class="form-control" type="text" name="exp_date" id="exp_date">
        </div>
    </div>

    <div class="col-4">
        <div class="form-group">
            <label for="amount">Amount</label>
            <input placeholder="points" class="form-control" type="number" name="amount" id="amount">
        </div>
    </div>
</div>
