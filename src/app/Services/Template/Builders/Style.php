<?php

namespace LaravelEnso\Tables\app\Services\Template\Builders;

use LaravelEnso\Tables\app\Attributes\Style as Attributes;

class Style
{
    private $template;
    private $defaultStyle;

    public function __construct($template)
    {
        $this->template = $template;
        $this->defaultStyle = config('enso.tables.style');
    }

    public function build()
    {
        $this->template->set('align', $this->compute(Attributes::Align));
        $this->template->set('style', $this->compute(Attributes::Table));
        $this->template->set('aligns', $this->preset(Attributes::Align));
        $this->template->set('styles', $this->preset(Attributes::Table));
        $this->template->set('highlight', $this->defaultStyle['highlight'] ?? null);
    }

    private function compute($style)
    {
        return collect($this->defaultStyle['default'])->intersect($style)
            ->values()
            ->reduce(function ($style, $param) {
                $style->push($this->defaultStyle['mapping'][$param]);

                return $style;
            }, collect())->unique()->implode(' ');
    }

    private function preset($style)
    {
        return collect($style)->reduce(function ($styles, $style) {
            $styles[$style] = $this->defaultStyle['mapping'][$style];

            return $styles;
        }, []);
    }
}
