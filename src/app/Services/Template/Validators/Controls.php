<?php

namespace LaravelEnso\Tables\app\Services\Template\Validators;

use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\app\Exceptions\ControlException;

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
            || $this->controls->filter(function ($control) {
                return ! is_string($control);
            })->isNotEmpty();

        if ($formattedWrong) {
            throw ControlException::invalidFormat();
        }

        return $this;
    }

    private function checkDefault()
    {
        $diff = $this->controls->diff($this->defaults);

        if ($diff->isNotEmpty()) {
            throw ControlException::undefined(
                $diff->implode('", "')
            );
        }

        return $this;
    }
}
