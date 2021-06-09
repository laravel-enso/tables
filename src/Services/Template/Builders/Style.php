<?php

namespace LaravelEnso\Tables\Services\Template\Builders;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\Tables\Attributes\Style as Attributes;

class Style
{
    private Obj $defaultStyle;

    public function __construct(private Obj $template)
    {
        $this->template = $template;
        $this->defaultStyle = new Obj(Config::get('enso.tables.style'));
    }

    public function build(): void
    {
        $this->template->set('align', $this->compute(Attributes::Align))
            ->set('style', $this->compute(Attributes::Table))
            ->set('aligns', $this->preset(Attributes::Align))
            ->set('styles', $this->preset(Attributes::Table))
            ->set('highlight', $this->defaultStyle->get('highlight'));
    }

    private function compute($style): Collection
    {
        return $this->defaultStyle->get('default')
            ->intersect($style)
            ->values()
            ->reduce(fn ($style, $param) => $style
                ->push($this->defaultStyle->get('mapping')->get($param)), new Collection())
            ->unique();
    }

    private function preset($style): Obj
    {
        return Collection::wrap($style)
            ->reduce(fn ($styles, $style) => $styles
                ->set($style, $this->defaultStyle->get('mapping')->get($style)), new Obj());
    }
}
