<?php

namespace LaravelEnso\Tables\app\Attributes;

class Column
{
    const Mandatory = ['data', 'label', 'name'];

    const Optional = ['align', 'class', 'dateFormat', 'enum', 'meta', 'money', 'tooltip'];

    const Meta = [
        'boolean', 'clickable', 'cents', 'customTotal', 'date', 'icon', 'notExportable', 'nullLast',
        'searchable', 'rawTotal', 'rogue', 'slot', 'sortable', 'sort:ASC', 'sort:DESC',
        'translatable', 'total',
    ];
}
