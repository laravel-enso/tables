@component('mail::message')
{{ __('Hi :name', ['name' => $name]) }},

@if(is_null($link))
{{ __('You will find the requested report attached to this email.') }}
@else
{{ __('To download the requested report click below or visit the app')}}

[{{ $filename }}]({!! $link !!})
@endif

{{ __('Thank you') }},<br>
{{ __(config('app.name')) }}
@endcomponent
