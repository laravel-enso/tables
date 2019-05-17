<?php

namespace LaravelEnso\Tables\app\Attributes;

class Button
{
    const Mandatory = ['type', 'icon', 'class'];

    const Optional = [
        'routeSuffix', 'action', 'fullRoute', 'label', 'method', 'confirmation',
        'event', 'message', 'params', 'postEvent', 'tooltip',
    ];

    const Actions = ['router', 'href', 'ajax', 'export'];

    const Methods = ['GET', 'PUT', 'PATCH', 'POST', 'DELETE'];
}
