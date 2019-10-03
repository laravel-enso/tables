<?php

namespace LaravelEnso\Tables\app\Attributes;

class Structure
{
    const Mandatory = ['routePrefix', 'columns', 'buttons'];

    const Optional = [
        'dtRowId', 'name', 'dataRouteSuffix', 'crtNo', 'appends', 'controls', 'buttons', 'lengthMenu',
        'auth', 'debounce', 'method', 'selectable', 'comparisonOperator', 'fullInfoRecordLimit',
        'countCache', 'templateCache', 'flatten', 'responsive', 'preview', 'searchModes', 'searchMode', 'model',
    ];
}
