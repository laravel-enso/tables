<?php

namespace LaravelEnso\Tables\Attributes;

class Button
{
    public const Mandatory = ['type', 'icon'];

    public const Optional = [
        'action', 'confirmation', 'event', 'fullRoute', 'label',  'message',
        'method', 'params', 'postEvent', 'routeSuffix', 'tooltip', 'slot',
        'class', 'name', 'selection',
    ];

    public const Actions = ['ajax', 'export', 'href', 'router'];

    public const Methods = ['DELETE', 'GET', 'PATCH', 'POST', 'PUT'];
}
