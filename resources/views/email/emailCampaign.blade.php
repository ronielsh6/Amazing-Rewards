@component('mail::message')
    <p>{{$campaign->body}}</p>

@component('mail::button', ['url' => $link])
    EARN NOW!!
@endcomponent
    <a href="{{$link_image}}">
        <img src="{{$campaign->image}}" alt="">
    </a>

    Thanks,
    Amazing Rewards
    Angel Development Group.
@endcomponent

