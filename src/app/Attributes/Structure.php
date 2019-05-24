<?php

namespace LaravelEnso\Tables\app\Attributes;

class Structure
{
    const Mandatory = ['routePrefix', 'columns'];

    const Optional = [
        'name', 'dataRouteSuffix', 'crtNo', 'appends', 'controls', 'buttons', 'lengthMenu', 'auth',
        'debounce', 'method', 'selectable', 'comparisonOperator', 'fullInfoRecordLimit', 'cache',
        'flatten', 'responsive', 'preview',
    ];
}
