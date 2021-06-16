<?php

namespace LaravelEnso\Tables\Services\Template\Builders;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use LaravelEnso\Helpers\Services\Obj;

class Structure
{
    private const DefaultFromConfig = [
        'comparisonOperator', 'dateFormat', 'debounce', 'dtRowId',
        'labels', 'lengthMenu', 'method', 'responsive', 'searchModes',
        'totalLabel',
    ];

    private const FalseIfMissing = [
        'selectable', 'preview',
    ];

    private const DefaultFalse = [
        'cents', 'date', 'datetime', 'enum', 'filterable', 'forceInfo', 'loading',
        'money', 'number', 'resource', 'searchable', 'sort', 'total', 'translatable',
    ];

    private const TemplateOrConfigToMeta = ['searchMode', 'fullInfoRecordLimit'];

    private bool $customDtRowId;

    public function __construct(
        private Obj $template,
        private Obj $meta
    ) {
        $this->customDtRowId = $this->template->has('dtRowId');
    }

    public function build(): void
    {
        $this->defaults()
            ->defaultFromConfig()
            ->falseIfMissing()
            ->name()
            ->readPath()
            ->length()
            ->templateOrConfigToMeta()
            ->defaultSort();
    }

    private function defaults(): self
    {
        $this->meta->set('start', 0);
        $this->meta->set('search', '');

        Collection::wrap(self::DefaultFalse)
            ->each(fn ($attribute) => $this->meta->set($attribute, false));

        return $this;
    }

    private function defaultFromConfig()
    {
        Collection::wrap(self::DefaultFromConfig)
            ->filter(fn ($attribute) => ! $this->template->has($attribute))
            ->each(fn ($attribute) => $this->template->set(
                $attribute,
                Config::get("enso.tables.{$attribute}")
            ));

        return $this;
    }

    private function falseIfMissing()
    {
        Collection::wrap(self::FalseIfMissing)
            ->filter(fn ($attribute) => ! $this->template->has($attribute))
            ->each(fn ($attribute) => $this->template->set($attribute, false));

        return $this;
    }

    private function name()
    {
        if (! $this->template->has('name')) {
            $this->template->set('name', Str::plural($this->template->get('model')));
        }

        return $this;
    }

    private function readPath(): self
    {
        $prefix = $this->template->get('routePrefix');

        $suffix = $this->template->get('dataRouteSuffix')
            ?? Config::get('enso.tables.dataRouteSuffix');

        $absolute = Config::get('enso.tables.absoluteRoutes');

        $this->template->set('readPath', route("{$prefix}.{$suffix}", [], $absolute));

        return $this;
    }

    private function length(): self
    {
        $this->meta->set(
            'length',
            $this->template->get('lengthMenu')[0]
        );

        return $this;
    }

    private function templateOrConfigToMeta(): self
    {
        Collection::wrap(self::TemplateOrConfigToMeta)
            ->each(fn ($attribute) => $this->metaFromTemplateOrConfig($attribute));

        return $this;
    }

    private function defaultSort(): void
    {
        if ($this->template->has('defaultSort')) {
            return;
        }

        $dtRowId = $this->template->get('dtRowId');

        $defaultSort = $this->customDtRowId
            ? $dtRowId
            : "{$this->template->get('table')}.{$dtRowId}";

        $this->template->set('defaultSort', $defaultSort);
    }

    private function metaFromTemplateOrConfig(string $attribute): void
    {
        $value = $this->template->get($attribute)
            ?? Config::get("enso.tables.{$attribute}");

        $this->meta->set($attribute, $value);

        $this->template->forget($attribute);
    }
}
