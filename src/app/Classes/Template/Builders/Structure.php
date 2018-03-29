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
        $this->computeRoutes()
            ->setLengthMenu()
            ->setDebounce()
            ->setDefaults();
    }

    private function computeRoutes()
    {
        $this->template->readPath = route($this->template->routePrefix.'.'.$this->template->readSuffix, [], false);
        $this->template->writePath = !is_null($this->template->writeSuffix)
            ? route($this->template->routePrefix.'.'.$this->template->writeSuffix, [], false)
            : null;

        return $this;
    }

    private function setLengthMenu()
    {
        if (!property_exists($this->template, 'lengthMenu')) {
            $this->template->lengthMenu = config('enso.datatable.lengthMenu');
        }

        return $this;
    }

    private function setDebounce()
    {
        if (!property_exists($this->template, 'debounce')) {
            $this->template->debounce = config('enso.datatable.debounce');
        }

        return $this;
    }

    private function setDefaults()
    {
        $this->template->total = false;
        $this->template->enum = false;
        $this->template->money = false;
        $this->template->date = false;
        $this->template->labels = config('enso.datatable.labels');
    }
}
