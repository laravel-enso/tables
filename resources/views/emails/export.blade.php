@component('mail::message')
{{ __('Hi :name', ['name' => $name]) }},

{{ __('You will find the export attached to this email.') }}

{{ __('Thank you') }},<br>
{{ __(config('app.name')) }}
@endcomponent