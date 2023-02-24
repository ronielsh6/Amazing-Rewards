@component('mail::message')
    <h2>Hello {{$user->name}},</h2>
    <p>Your verification code is {{$user->email_verification_code}}</p>

{{--    <p>Visit @component('mail::button', ['url' => $body['url_b']])--}}
{{--            Laravel Tutorials--}}
{{--        @endcomponent and learn more about the Laravel framework.</p>--}}

    Thanks,
    Amazing Rewards
    Angel Development Group.
@endcomponent

