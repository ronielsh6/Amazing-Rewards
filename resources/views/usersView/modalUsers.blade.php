<form id="user-message-form" action="">
    <div class="col-12">
        <label for="messageTitle">Message title</label>
        <input type="text" class="form-control form-custom" id="messageTitle" name="messageTitle">
    </div>
    <div class="col-12">
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
                <option value="overlay_permission_request">Lock Screen Permission</option>
                <option value="app_update">App Update</option>
            </select>
        </div>
    </div>
    <div class="col-12">
        <label for="messageBody">Message body</label>
        <textarea name="messageBody" id="messageBody" class="form-control form-custom" rows="10"></textarea>
    </div>
    <input type="hidden" value="{{ route('sendMessages') }}" id="messagesRoute">
    <input type="hidden" value="0" id="customId">
</form>
