<?php

namespace LaravelEnso\VueDatatable\app\Classes\Template;

use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\VueDatatable\app\Classes\Template\Builders\Controls;
use LaravelEnso\VueDatatable\app\Classes\Template\Builders\Style;
use LaravelEnso\VueDatatable\app\Classes\Template\Builders\Buttons;
use LaravelEnso\VueDatatable\app\Classes\Template\Builders\Columns;
use LaravelEnso\VueDatatable\app\Classes\Template\Builders\Structure;

class Builder
{
    private $template;
    private $meta;

    public function __construct(Obj $template, Obj $meta)
    {
        $this->template = $template;
        $this->meta = $meta;
    }

    public function run()
    {
        (new Structure($this->template, $this->meta))
            ->build();

        (new Columns($this->template, $this->meta))
            ->build();

        (new Buttons($this->template, $this->meta))
            ->build();

        (new Style($this->template))
            ->build();

        (new Controls($this->template))
            ->build();

        $this->cleanUp();
    }

    private function cleanUp()
    {
        $this->template->forget(['dataRouteSuffix', 'routePrefix']);
    }
}
