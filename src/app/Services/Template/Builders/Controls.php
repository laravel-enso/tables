<?php

namespace LaravelEnso\Tables\App\Services\Template\Builders;

use LaravelEnso\Helpers\App\Classes\Obj;
use LaravelEnso\Tables\App\Attributes\Controls as Attributes;

class Controls
{
    private Obj $template;
    private array $defaultControls;

    public function __construct(Obj $template)
    {
        $this->template = $template;
        $this->defaultControls = config('enso.tables.controls');
    }

    public function build(): void
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
