<?php

namespace LaravelEnso\Tables\App\Services\Template\Validators;

use LaravelEnso\Helpers\App\Classes\Obj;
use LaravelEnso\Tables\App\Exceptions\Control as Exception;

class Controls
{
    private $controls;
    private $defaults;

    public function __construct(Obj $template)
    {
        $this->controls = $template->get('controls');
        $this->defaults = new Obj(config('enso.tables.controls'));
    }

    public function validate()
    {
        if ($this->controls !== null) {
            $this->checkFormat()
                ->checkDefault();
        }
    }

    private function checkFormat()
    {
        $formattedWrong = ! $this->controls instanceof Obj
            || $this->controls
                ->filter(fn ($control) => ! is_string($control))
                ->isNotEmpty();

        if ($formattedWrong) {
            throw Exception::invalidFormat();
        }

        return $this;
    }

    private function checkDefault()
    {
        $diff = $this->controls->diff($this->defaults);

        if ($diff->isNotEmpty()) {
            throw Exception::undefined(
                $diff->implode('", "')
            );
        }

        return $this;
    }
}
