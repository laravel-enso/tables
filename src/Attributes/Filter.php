<?php

namespace LaravelEnso\Tables\Attributes;

class Filter
{
    public const Mandatory = ['label', 'data', 'value', 'type'];

    public const Optional = [
        'slot', 'multiple', 'route', 'translated', 'params',
        'pivotParams', 'custom', 'selectLabel',
    ];
}
