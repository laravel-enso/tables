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
        $this->routes()
            ->lengthMenu()
            ->debounce()
            ->method()
            ->defaults();
    }

    private function routes()
    {
        $this->template->readPath =
            route($this->template->routePrefix.'.'.$this->template->readSuffix, [], false);

        return $this;
    }

    private function lengthMenu()
    {
        if (!property_exists($this->template, 'lengthMenu')) {
            $this->template->lengthMenu = config('enso.datatable.lengthMenu');
        }

        return $this;
    }

    private function debounce()
    {
        if (!property_exists($this->template, 'debounce')) {
            $this->template->debounce = config('enso.datatable.debounce');
        }

        return $this;
    }

    private function method()
    {
        if (!isset($this->template->method)) {
            $this->template->method = config('enso.datatable.method');
        }

        return $this;
    }

    private function defaults()
    {
        $this->template->total = false;
        $this->template->enum = false;
        $this->template->money = false;
        $this->template->date = false;
        $this->template->searchable = false;
        $this->template->labels = config('enso.datatable.labels');
    }
}
