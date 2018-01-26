<?php

namespace LaravelEnso\VueDatatable\app\Classes\Template\Validators;

use Symfony\Component\Routing\Route;
use LaravelEnso\VueDatatable\app\Exceptions\TemplateException;

class Routes
{
    private $prefix;
    private $readSuffix;
    private $writeSuffix;

    public function __construct($template)
    {
        $this->prefix = $template->routePrefix;
        $this->readSuffix = $template->readSuffix;
        $this->writeSuffix = property_exists($template, 'readSuffix')
            ? $template->writeSuffix
            : null;
    }

    public function validate()
    {
        $this->checkReadRoute()
            ->checkWriteRoute();
    }

    private function checkReadRoute()
    {
        $readRoute = $this->prefix.'.'.$this->readSuffix;

        if (!\Route::has($readRoute)) {
            throw new TemplateException(__(
                'Read route does not exist: ":route"',
                ['route' => $readRoute]
            ));
        }

        return $this;
    }

    private function checkWriteRoute()
    {
        $writeRoute = !is_null($this->writeSuffix)
            ? $this->prefix.'.'.$this->writeSuffix
            : null;

        if ($writeRoute && !\Route::has($writeRoute)) {
            throw new TemplateException(__(
                'Write route does not exist: ":route"',
                ['route' => $writeRoute]
            ));
        }
    }
}
