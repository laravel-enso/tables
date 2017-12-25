<?php

namespace LaravelEnso\VueDatatable\app\Classes\Template\Builders;

class Structure
{
    private $template;

    public function __construct($template)
    {
        $this->template = $template;
    }

    public function build()
    {
        $this->computeRoutes();
        $this->setLengthMenu();
        $this->setDefaults();
    }

    private function computeRoutes()
    {
        $this->template->readPath = route($this->template->routePrefix.'.'.$this->template->readSuffix, [], false);
        $this->template->writePath = !is_null($this->template->writeSuffix)
            ? route($this->template->routePrefix.'.'.$this->template->writeSuffix, [], false)
            : null;
    }

    private function setLengthMenu()
    {
        if (!property_exists($this->template, 'lengthMenu')) {
            $this->template->lengthMenu = config('enso.datatable.lengthMenu');
        }
    }

    private function setDefaults()
    {
        $this->template->total = false;
        $this->template->enum = false;
        $this->template->date = false;
        $this->template->labels = config('enso.datatable.labels');
        $this->template->boolean = (object) config('enso.datatable.boolean');
    }
}
