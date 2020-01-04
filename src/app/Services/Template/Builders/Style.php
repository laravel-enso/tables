<?php

namespace LaravelEnso\Tables\App\Services\Template\Builders;

use Illuminate\Support\Collection;
use LaravelEnso\Helpers\App\Classes\Obj;
use LaravelEnso\Tables\App\Attributes\Style as Attributes;

class Style
{
    private Obj $template;
    private Obj $defaultStyle;

    public function __construct(Obj $template)
    {
        $this->template = $template;
        $this->defaultStyle = new Obj(config('enso.tables.style'));
    }

    public function build(): void
    {
        $this->template->set('align', $this->compute(Attributes::Align));
        $this->template->set('style', $this->compute(Attributes::Table));
        $this->template->set('aligns', $this->preset(Attributes::Align));
        $this->template->set('styles', $this->preset(Attributes::Table));
        $this->template->set('highlight', $this->defaultStyle->get('highlight'));
    }

    private function compute($style): string
    {
        return $this->defaultStyle->get('default')
            ->intersect($style)
            ->values()
            ->reduce(fn ($style, $param) => $style
                ->push($this->defaultStyle->get('mapping')->get($param)), new Collection())
            ->unique()
            ->implode(' ');
    }

    private function preset($style): Obj
    {
        return (new Collection($style))->reduce(fn ($styles, $style) => $styles
            ->set($style, $this->defaultStyle->get('mapping')->get($style)), new Obj());
    }
}
