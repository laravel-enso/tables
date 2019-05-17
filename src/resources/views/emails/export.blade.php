@component('mail::message')
{{ __('Hi :name', ['name' => $name]) }},

@if($link === null)
{{ __('You will find the report attached to this email.') }}
@else
{{ __('Your report has :entries entries', ['entries' => $entries]) }}.

{{ __('To download the report click below or visit the app') }}

[{{ $filename }}]({!! $link !!})
@endif

{{ __('Thank you') }},<br>
{{ __(config('app.name')) }}
@endcomponent
