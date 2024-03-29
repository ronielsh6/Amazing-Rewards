<div class="row">
    <input type="hidden" id="createUri" value="{{ route('createCampaign') }}">
    <input type="hidden" id="updateUri" value="{{ route('updateCampaign') }}">
    <input type="hidden" id="method" value="POST">
    <input type="hidden" id="campaign_id" name="campaign_id" value="0">
    <div class="col-6">
        <div id="builder"></div>
    </div>
    <div class="col-6">
        <ul class="list-group">
            <li class="list-group-item border-0 px-0">
                <div class="form-check form-switch ps-0">
                    <input class="form-check-input ms-auto" type="checkbox" id="flexSwitchCheckPush" checked>
                    <label class="form-check-label text-body ms-3 text-truncate w-80 mb-0" for="flexSwitchCheckPush">Send By Push Notification</label>
                </div>
            </li>
            <li class="list-group-item border-0 px-0">
                <div class="form-check form-switch ps-0">
                    <input class="form-check-input ms-auto" type="checkbox" id="flexSwitchCheckEmail">
                    <label class="form-check-label text-body ms-3 text-truncate w-80 mb-0" for="flexSwitchCheckEmail">Send by Email</label>
                </div>
            </li>
        </ul>
        <label for="csv">Campaign Image:</label>
        <input type="file" name="csv" class="filepond"/>
    </div>
    <div class="col-6" id="image_links_container" hidden= "true">
        <div class="form-group">
            <label for="image_link">Image link</label>
            <select class="form-control select2" name="image_link" id="image_link">
                <option value="news_screen">News Screen</option>
                <option value="earn_screen">Earn Screen</option>
                <option value="play_games">Games</option>
                <option value="take_surrveys">Surveys</option>
                <option value="shop_screen">Shop Screen</option>
                <option value="rewards_screen">Rewards Screen</option>
                <option value="settings_screen">Settings Screen</option>
                <option value="fluent_sephora">$750 to Sephora</option>
                <option value="fluent_amazon">$750 to Amazon</option>
                <option value="fluent_cash">$750 to Cash</option>
                <option value="overlay_permission_request">Lock Screen Permission</option>
                <option value="app_update">App Update</option>
            </select>
        </div>
    </div>
    <div class="col-12">
        <span class="text-danger">Campaign will impact on </span><span id="impact-indicator" class="text-danger"> - users</span>
    </div>
    <div class="col-6">
        <div class="form-group">
            <label for="execution_time">Execution time</label>
            <input class="form-control timepicker" type="time" name="execution_time" id="execution_time">
        </div>
    </div>
    <div class="col-6">
        <div class="form-group">
            <label for="deep_link">Deep links</label>
            <select class="form-control select2" name="deep_links" id="deep_link">
                <option value="news_screen">News Screen</option>
                <option value="earn_screen">Earn Screen</option>
                <option value="play_games">Games</option>
                <option value="take_surrveys">Surveys</option>
                <option value="shop_screen">Shop Screen</option>
                <option value="rewards_screen">Rewards Screen</option>
                <option value="settings_screen">Settings Screen</option>
                <option value="fluent_sephora">$750 to Sephora</option>
                <option value="fluent_amazon">$750 to Amazon</option>
                <option value="fluent_cash">$750 to Cash</option>
                <option value="overlay_permission_request">Lock Screen Permission</option>
                <option value="app_update">App Update</option>
            </select>
        </div>
    </div>
    <div class="col-4">
        <div class="form-group">
            <label for="start_date">Starting date</label>
            <input class="form-control" type="text" name="start_date" id="start_date">
        </div>
    </div>
    <div class="col-4">
        <div class="form-group">
            <label for="end_date">Ending date</label>
            <input class="form-control" type="text" name="end_date" id="end_date">
        </div>
    </div>
    <div class="col-4">
        <div class="form-group">
            <label for="frequency">Frequency</label>
            <select class="form-control frequency" name="frequency" id="frequency">
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
