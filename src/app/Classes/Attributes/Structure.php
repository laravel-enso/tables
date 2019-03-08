<?php

namespace LaravelEnso\VueDatatable\app\Classes\Attributes;

class Structure
{
    const Mandatory = ['routePrefix', 'columns'];

    const Optional = [
        'name', 'dataRouteSuffix', 'crtNo', 'appends', 'buttons', 'lengthMenu', 'auth', 'debounce', //TODO implement name
        'method', 'selectable', 'comparisonOperator', 'fullInfoRecordLimit', 'cache', 'flatten'
    ];
}
