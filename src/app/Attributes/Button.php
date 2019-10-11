<?php

namespace LaravelEnso\Tables\app\Attributes;

class Button
{
    const Mandatory = ['class', 'icon', 'type'];

    const Optional = [
        'action', 'confirmation', 'event', 'fullRoute', 'label',  'message',
        'method', 'params', 'postEvent', 'routeSuffix', 'tooltip',
    ];

    const Actions = ['ajax', 'export', 'href', 'router'];

    const Methods = ['DELETE', 'GET', 'PATCH', 'POST', 'PUT'];
}
