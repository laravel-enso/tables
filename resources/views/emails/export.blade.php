@component('mail::message')
{{ __('Hi :name', ['name' => $name]) }},

@if($link === null)
{{ __('You will find the export attached to this email.') }}
@else
{{ __('Your :filename has :entries entries', ['filename' => $filename, 'entries' => $entries]) }}.

@component('mail::button', ['url' => $link])
@lang('Download file')
@endcomponent
@endif

{{ __('Thank you') }},<br>
{{ __(config('app.name')) }}
@endcomponent