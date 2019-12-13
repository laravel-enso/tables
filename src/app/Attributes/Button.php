<?php

namespace LaravelEnso\Tables\app\Attributes;

class Button
{
    public const Mandatory = ['class', 'icon', 'type'];

    public const Optional = [
        'action', 'confirmation', 'event', 'fullRoute', 'label',  'message',
        'method', 'params', 'postEvent', 'routeSuffix', 'tooltip',
    ];

    public const Actions = ['ajax', 'export', 'href', 'router'];

    public const Methods = ['DELETE', 'GET', 'PATCH', 'POST', 'PUT'];
}
