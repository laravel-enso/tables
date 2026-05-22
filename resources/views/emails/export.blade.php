@component('mail::message')
@component('mail::title')
{{ __('Table export done') }}
@endcomponent

{{ __('Hi :name', ['name' => $name]) }},

{{ __('You will find the export attached to this email.') }}

@component('mail::file', [
    'meta' => __(':entries entries', ['entries' => $entries]),
    'url' => $url ?? null,
])
{{ $filename }}
@endcomponent

@component('mail::signature')
@endcomponent
@endcomponent
