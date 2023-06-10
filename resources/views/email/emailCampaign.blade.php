@component('mail::message')
    <p>{{$campaign->body}}</p>

@component('mail::button', ['url' => $link])
    EARN NOW!!
@endcomponent


    Thanks,
    Amazing Rewards
    Angel Development Group.
@endcomponent

