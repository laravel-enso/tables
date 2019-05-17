<?php

namespace LaravelEnso\Tables\app\Attributes;

class Column
{
    const Mandatory = ['label', 'name', 'data'];

    const Optional = ['meta', 'enum', 'tooltip', 'money', 'class', 'align', 'dateFormat'];

    const Meta = [
        'searchable', 'sortable', 'sort:ASC', 'sort:DESC', 'translatable',
        'boolean', 'slot', 'rogue', 'total', 'date', 'icon', 'clickable',
        'customTotal', 'notExportable', 'nullLast',
    ];
}
