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
        $this->template->readRoute = route($this->template->routePrefix.'.'.$this->template->readSuffix, [], false);
        $this->template->writeRoute = !is_null($this->template->writeSuffix)
            ? route($this->template->routePrefix.'.'.$this->template->writeSuffix, [], false)
            : null;

        unset($this->template->readSuffix, $this->template->writeSuffix, $this->template->routePrefix);
    }

    private function setLengthMenu()
    {
        if (!property_exists($this->template, 'lengthMenu') || !is_array($this->template->lengthMenu)) {
            $this->template->lengthMenu = config('enso.datatables.lengthMenu');
        }
    }

    private function setDefaults()
    {
        $this->template->sort = false;
        $this->template->labels = config('enso.datatables.labels');
        $this->template->boolean = (object) config('enso.datatables.boolean');
    }
}
