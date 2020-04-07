<?php

namespace LaravelEnso\Tables\App\Attributes;

class Filter
{
    public const Mandatory = ['label', 'data', 'value', 'type'];

    public const Optional = ['slot', 'multiple', 'route', 'translated'];
}
