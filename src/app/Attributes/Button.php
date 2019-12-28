<?php

namespace LaravelEnso\Tables\app\Attributes;

class Button
{
    public const Mandatory = ['type'];

    public const Optional = [
        'action', 'confirmation', 'event', 'fullRoute', 'label',  'message',
        'method', 'params', 'postEvent', 'routeSuffix', 'tooltip', 'slot',
        'class', 'icon', 'name'
    ];

    public const Actions = ['ajax', 'export', 'href', 'router'];

    public const Methods = ['DELETE', 'GET', 'PATCH', 'POST', 'PUT'];
}
