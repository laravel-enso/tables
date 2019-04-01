<?php

namespace LaravelEnso\VueDatatable\app\Classes\Template\Builders;

use LaravelEnso\VueDatatable\app\Classes\Attributes\Controls as Attributes;

class Controls
{
    private $template;
    private $defaultControls;

    public function __construct($template)
    {
        $this->template = $template;
        $this->defaultControls = config('enso.datatable.controls');
    }

    public function build()
    {
        if (!$this->template->has('controls')) {
            if ($this->defaultControls) {
                $this->template->set('controls', $this->defaultControls);
            } else {
                $this->template->set('controls', Attributes::List);
            }
        } else {
            $this->template->set('controls', $this->template->get('controls'));
        }
    }

    private function compute($controls)
    {
        return collect($this->defaultControls)->intersect($controls)
            ->values()->unique()->implode(' ');
    }
}
