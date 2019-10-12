<?php

namespace LaravelEnso\Tables\app\Attributes;

class Structure
{
    const Mandatory = ['buttons', 'columns', 'routePrefix'];

    const Optional = [
        'appends', 'auth', 'buttons', 'controls', 'comparisonOperator', 'countCache',
        'crtNo', 'dataRouteSuffix', 'debounce', 'dtRowId', 'flatten', 'fullInfoRecordLimit',
        'lengthMenu', 'method', 'model', 'name',  'preview', 'responsive', 'searchMode',
        'searchModes', 'selectable', 'templateCache',
    ];
}
