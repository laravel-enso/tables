<?php

namespace LaravelEnso\Tables\App\Services\Template\Builders;

use Illuminate\Support\Str;
use LaravelEnso\Helpers\App\Classes\Obj;

class Structure
{
    private Obj $template;
    private Obj $meta;

    public function __construct(Obj $template, Obj $meta)
    {
        $this->template = $template;
        $this->meta = $meta;
    }

    public function build(): void
    {
        $this->readPath()
            ->dtRowId()
            ->lengthMenu()
            ->debounce()
            ->method()
            ->selectable()
            ->preview()
            ->comparisonOperator()
            ->searchMode()
            ->fullInfoRecordLimit()
            ->responsive()
            ->defaults();
    }

    private function readPath(): self
    {
        $route = $this->template->get('routePrefix').'.'
            .($this->template->get('dataRouteSuffix')
                ?? config('enso.tables.dataRouteSuffix'));

        $this->template->set('readPath', route($route, [], false));

        return $this;
    }

    private function dtRowId(): self
    {
        if (! $this->template->has('dtRowId')) {
            $this->template->set('dtRowId', config('enso.tables.dtRowId'));
        }

        return $this;
    }

    private function lengthMenu(): self
    {
        if (! $this->template->has('lengthMenu')) {
            $this->template->set('lengthMenu', config('enso.tables.lengthMenu'));
        }

        $this->meta->set(
            'length', $this->template->get('lengthMenu')[0]
        );

        return $this;
    }

    private function debounce(): self
    {
        if (! $this->template->has('debounce')) {
            $this->template->set('debounce', config('enso.tables.debounce'));
        }

        return $this;
    }

    private function method(): self
    {
        if (! $this->template->has('method')) {
            $this->template->set('method', config('enso.tables.method'));
        }

        return $this;
    }

    private function selectable(): self
    {
        if (! $this->template->has('selectable')) {
            $this->template->set('selectable', false);
        }

        return $this;
    }

    private function preview(): self
    {
        if (! $this->template->has('preview')) {
            $this->template->set('preview', false);
        }

        return $this;
    }

    private function defaults(): void
    {
        if (! $this->template->has('name')) {
            $this->template->set('name', Str::plural($this->template->get('model')));
        }

        $this->template->set('labels', config('enso.tables.labels'));
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
        $this->meta->set('cents', false);
        $this->meta->set('money', false);
    }

    private function comparisonOperator(): self
    {
        if (! $this->template->has('comparisonOperator')) {
            $this->template->set('comparisonOperator', config('enso.tables.comparisonOperator'));
        }

        return $this;
    }

    private function searchMode(): self
    {
        $this->meta->set(
            'searchMode',
            $this->template->get('searchMode')
                ?? config('enso.tables.searchMode')
        );

        if (! $this->template->has('searchModes')) {
            $this->template->set('searchModes', config('enso.tables.searchModes'));
        }

        $this->template->forget('searchMode');

        return $this;
    }

    private function fullInfoRecordLimit(): self
    {
        $this->meta->set(
            'fullInfoRecordLimit',
            $this->template->get('fullInfoRecordLimit')
                ?? config('enso.tables.fullInfoRecordLimit')
        );

        $this->template->forget('fullInfoRecordLimit');

        return $this;
    }

    private function responsive(): self
    {
        if (! $this->template->has('responsive')) {
            $this->template->set('responsive', config('enso.tables.responsive'));
        }

        return $this;
    }
}
