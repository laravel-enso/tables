<?php

namespace LaravelEnso\VueDatatable\app\Classes\Template;

use LaravelEnso\VueDatatable\app\Classes\Template\Builders\Style;
use LaravelEnso\VueDatatable\app\Classes\Template\Builders\Buttons;
use LaravelEnso\VueDatatable\app\Classes\Template\Builders\Columns;
use LaravelEnso\VueDatatable\app\Classes\Template\Builders\Structure;

class Builder
{
    private $template;

    public function __construct($template)
    {
        $this->template = $template;
    }

    public function run()
    {
        (new Structure($this->template))
            ->build();

        (new Columns($this->template))
            ->build();

        (new Buttons($this->template))
            ->build();

        (new Style($this->template))
            ->build();

        $this->cleanUp();
    }

    private function cleanUp()
    {
        unset(
            $this->template->readSuffix,
            $this->template->routePrefix
        );
    }
}
