@component('mail::message')
# verifi {{$user->name}}


Verify your acount;

Your verification code id {{$user->verification_code}}
@component('mail::button', ['url' => route('verify',$user->id)])
Verify Now
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent

<!-- Hello {{$user->name}}

Verify your acount;

Your verification code id {{$user->verification_code}} -->
