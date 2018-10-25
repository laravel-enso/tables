<?php

namespace LaravelEnso\VueDatatable\app\Classes\Attributes;

class Structure
{
    const Mandatory = ['routePrefix', 'columns'];

    const Optional = [
        'dataRouteSuffix', 'crtNo', 'appends', 'buttons', 'lengthMenu',
        'auth', 'debounce', 'method', 'selectable',
    ];
}
