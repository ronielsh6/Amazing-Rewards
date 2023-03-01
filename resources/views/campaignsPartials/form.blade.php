<div class="row">
    <input type="hidden" id="createUri" value="{{ route('createCampaign') }}">
    <input type="hidden" id="updateUri" value="{{ route('updateCampaign') }}">
    <input type="hidden" id="method" value="POST">
    <input type="hidden" id="campaign_id" name="campaign_id" value="0">
    <div class="col-12">
        <div id="builder"></div>
    </div>
    <div class="col-6">
        <div class="form-group">
            <label for="execution_time">Execution time</label>
            <input class="form-control timepicker" type="time" name="execution_time" id="execution_time">
        </div>
    </div>
    <div class="col-6">
        <div class="form-group">
            <label for="start_date">Starting date</label>
            <input class="form-control" type="text" name="start_date" id="start_date">
        </div>
    </div>
    <div class="col-6">
        <div class="form-group">
            <label for="end_date">Ending date</label>
            <input class="form-control" type="text" name="end_date" id="end_date">
        </div>
    </div>
    <div class="col-6">
        <div class="form-group">
            <label for="frequency">Frequency</label>
            <select class="form-control select2" name="frequency" id="frequency">
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
                <option value="yearly">Yearly</option>
            </select>
        </div>
    </div>
    <div class="col12">
        <div class="form-group">
            <label for="title">Message title</label>
            <input type="text" id="title" name="title" class="form-control">
        </div>
    </div>
    <div class="col12">
        <div class="form-group">
            <label for="body">Message body</label>
            <textarea id="body" name="body" class="form-control">
            </textarea>
        </div>
    </div>
</div>
