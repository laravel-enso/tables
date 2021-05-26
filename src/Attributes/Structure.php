<?php

namespace LaravelEnso\Tables\Attributes;

class Structure
{
    public const Mandatory = ['buttons', 'columns', 'routePrefix'];

    public const Optional = [
        'appends', 'auth', 'buttons', 'controls', 'comparisonOperator', 'countCache',
        'crtNo', 'dataRouteSuffix', 'debounce', 'dtRowId', 'filters', 'flatten',
        'fullInfoRecordLimit', 'lengthMenu', 'method', 'model', 'name',  'preview',
        'responsive', 'searchMode', 'searchModes', 'selectable', 'strip', 'templateCache',
        'defaultSort', 'totalLabel',
    ];
}
