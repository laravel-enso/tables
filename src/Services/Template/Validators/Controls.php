<?php

namespace LaravelEnso\Tables\Services\Template\Validators;

use Illuminate\Support\Facades\Config;
use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\Tables\Exceptions\Control as Exception;

class Controls
{
    private ?Obj $controls;
    private Obj $defaults;

    public function __construct(Obj $template)
    {
        $this->controls = $template->get('controls');
        $this->defaults = new Obj(Config::get('enso.tables.controls'));
    }

    public function validate()
    {
        if ($this->controls !== null) {
            $this->format()
                ->defaults();
        }
    }

    private function format()
    {
        if ($this->invalidFormat()) {
            throw Exception::invalidFormat();
        }

        return $this;
    }

    private function invalidFormat()
    {
        return ! $this->controls instanceof Obj || $this->controls
            ->filter(fn ($control) => ! is_string($control))
            ->isNotEmpty();
    }

    private function defaults()
    {
        $diff = $this->controls->diff($this->defaults);

        if ($diff->isNotEmpty()) {
            throw Exception::undefined($diff->implode('", "'));
        }

        return $this;
    }
}
