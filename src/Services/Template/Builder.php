<?php

namespace LaravelEnso\Tables\Services\Template;

use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\Tables\Services\Template\Builders\Buttons;
use LaravelEnso\Tables\Services\Template\Builders\Columns;
use LaravelEnso\Tables\Services\Template\Builders\Controls;
use LaravelEnso\Tables\Services\Template\Builders\Filters;
use LaravelEnso\Tables\Services\Template\Builders\Structure;
use LaravelEnso\Tables\Services\Template\Builders\Style;

class Builder
{
    public function __construct(
        private Obj $template,
        private Obj $meta
    ) {
    }

    public function handleCacheable()
    {
        (new Structure($this->template, $this->meta))->build();

        (new Columns($this->template, $this->meta))->build();

        (new Style($this->template))->build();

        (new Controls($this->template))->build();
    }

    public function handleNonCacheable()
    {
        (new Buttons($this->template))->build();

        (new Filters($this->template, $this->meta))->build();

        $this->template->forget(['dataRouteSuffix', 'routePrefix']);
    }
}
