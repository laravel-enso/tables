<?php

namespace LaravelEnso\Tables\app\Services\Template\Builders;

use LaravelEnso\Tables\app\Attributes\Controls as Attributes;

class Controls
{
    private $template;
    private $defaultControls;

    public function __construct($template)
    {
        $this->template = $template;
        $this->defaultControls = config('enso.tables.controls');
    }

    public function build()
    {
        if (! $this->template->has('controls')) {
            if ($this->defaultControls) {
                $this->template->set(
                    'controls', $this->defaultControls ?? Attributes::List
                );
            }
        }
    }
}
