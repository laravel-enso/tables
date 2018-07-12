<?php

namespace LaravelEnso\VueDatatable\app\Classes\Attributes;

class Column
{
    const Mandatory = ['label', 'name', 'data'];

    const Optional = ['meta', 'enum', 'tooltip', 'money', 'class', 'align', 'dateFormat'];
}
