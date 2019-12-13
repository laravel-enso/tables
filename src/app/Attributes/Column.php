<?php

namespace LaravelEnso\Tables\app\Attributes;

class Column
{
    public const Mandatory = ['data', 'label', 'name'];

    public const Optional = ['align', 'class', 'dateFormat', 'enum', 'meta', 'money', 'tooltip'];

    public const Meta = [
        'boolean', 'clickable', 'cents', 'customTotal', 'date', 'icon', 'notExportable', 'nullLast',
        'searchable', 'rawTotal', 'rogue', 'slot', 'sortable', 'sort:ASC', 'sort:DESC',
        'translatable', 'total',
    ];
}
