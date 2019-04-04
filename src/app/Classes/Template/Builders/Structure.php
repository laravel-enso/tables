<?php

namespace LaravelEnso\VueDatatable\app\Classes\Template\Builders;

use Illuminate\Support\Str;
use LaravelEnso\Helpers\app\Classes\Obj;

class Structure
{
    private $template;
    private $meta;

    public function __construct(Obj $template, Obj $meta)
    {
        $this->template = $template;
        $this->meta = $meta;
    }

    public function build()
    {
        $this->readPath()
            ->lengthMenu()
            ->debounce()
            ->method()
            ->selectable()
            ->comparisonOperator()
            ->fullInfoRecordLimit()
            ->responsive()
            ->defaults();
    }

    private function readPath()
    {
        $route = $this->template->get('routePrefix').'.'.(
            $this->template->has('dataRouteSuffix')
                ? $this->template->get('dataRouteSuffix')
                : config('enso.datatable.dataRouteSuffix')
        );

        $this->template->set('readPath', route($route, [], false));

        return $this;
    }

    private function lengthMenu()
    {
        if (! $this->template->has('lengthMenu')) {
            $options = config('enso.datatable.lengthMenu');
            $this->template->set('lengthMenu', $options);
        }

        $this->meta->set(
            'length', $this->template->get('lengthMenu')[0]
        );

        return $this;
    }

    private function debounce()
    {
        if (! $this->template->has('debounce')) {
            $this->template->set('debounce', config('enso.datatable.debounce'));
        }

        return $this;
    }

    private function method()
    {
        if (! $this->template->has('method')) {
            $this->template->set('method', config('enso.datatable.method'));
        }

        return $this;
    }

    private function selectable()
    {
        if (! $this->template->has('selectable')) {
            $this->template->set('selectable', false);
        }

        return $this;
    }

    private function defaults()
    {
        $this->template->set('labels', config('enso.datatable.labels'));
        $this->template->set('pathSegment', $this->pathSegment());
        $this->meta->set('start', 0);
        $this->meta->set('search', '');
        $this->meta->set('loading', false);
        $this->meta->set('forceInfo', false);
        $this->meta->set('searchable', false);
        $this->meta->set('sort', false);
        $this->meta->set('total', false);
        $this->meta->set('date', false);
        $this->meta->set('translatable', false);
        $this->meta->set('enum', false);
        $this->meta->set('money', false);
    }

    private function pathSegment()
    {
        $segment = collect(
                explode('.', $this->template->get('routePrefix'))
            )->last();

        return Str::singular($segment);
    }

    private function comparisonOperator()
    {
        $this->meta->set(
            'comparisonOperator',
            $this->template->has('comparisonOperator')
                ? $this->template->get('comparisonOperator')
                : config('enso.datatable.comparisonOperator')
        );

        $this->template->forget('comparisonOperator');

        return $this;
    }

    private function fullInfoRecordLimit()
    {
        $this->meta->set(
            'fullInfoRecordLimit',
            $this->template->get('fullInfoRecordLimit')
                ?? config('enso.datatable.fullInfoRecordLimit')
        );

        $this->template->forget('fullInfoRecordLimit');

        return $this;
    }

    private function responsive()
    {
        if (! $this->template->has('responsive')) {
            $this->template->set('responsive', config('enso.datatable.responsive'));
        }

        return $this;
    }
}
