<?php

namespace LaravelEnso\VueDatatable\app\Classes\Template\Builders;

class Style
{
    private const Table = ['compact', 'hover', 'striped', 'bordered'];
    private const Align = ['center', 'left', 'right'];

    private $template;
    private $style;

    public function __construct($template)
    {
        $this->template = $template;
        $this->style = config('enso.datatable.style');
    }

    public function build()
    {
        $this->template->align = $this->compute(self::Align);
        $this->template->style = $this->compute(self::Table);
        $this->template->aligns = $this->getPresets(self::Align);
        $this->template->styles = $this->getPresets(self::Table);
    }

    private function compute($style)
    {
        return collect($this->style['default'])->intersect($style)
            ->values()
            ->reduce(function ($style, $param) {
                $style->push($this->style['mapping'][$param]);

                return $style;
            }, collect())->unique()->implode(' ');
    }

    private function getPresets($style)
    {
        return collect($style)->reduce(function ($styles, $style) {
            $styles[$style] = $this->style['mapping'][$style];

            return $styles;
        }, []);
    }
}
