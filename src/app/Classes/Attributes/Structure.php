<?php

namespace LaravelEnso\VueDatatable\app\Classes\Attributes;

class Structure
{
    const Mandatory = ['routePrefix', 'readSuffix', 'columns'];

    const Optional = [
        'name', 'icon', 'crtNo', 'appends', 'buttons',
        'lengthMenu', 'auth', 'debounce', 'method',
    ];
}
