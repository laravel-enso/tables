<?php

namespace LaravelEnso\Tables\Services\Template\Builders;

use Illuminate\Support\Facades\Config;
use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\Tables\Attributes\Controls as Attributes;

class Controls
{
    public function __construct(private Obj $template)
    {
    }

    public function build(): void
    {
        if (! $this->template->has('controls')) {
            $this->template->set(
                'controls',
                Config::get('enso.tables.controls')
                    ?? Attributes::List
            );
        }
    }
}
