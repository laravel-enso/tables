<?php

namespace LaravelEnso\VueDatatable\app\Classes\Template\Validators;

use Symfony\Component\Routing\Route;
use LaravelEnso\VueDatatable\app\Exceptions\TemplateException;

class Routes
{
    private $prefix;
    private $readSuffix;

    public function __construct($template)
    {
        $this->prefix = $template->routePrefix;
        $this->readSuffix = $template->readSuffix;
    }

    public function validate()
    {
        $readRoute = $this->prefix.'.'.$this->readSuffix;

        if (!\Route::has($readRoute)) {
            throw new TemplateException(__(
                'Read route does not exist: ":route"',
                ['route' => $readRoute]
            ));
        }
    }
}
