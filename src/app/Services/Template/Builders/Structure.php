<?php

namespace LaravelEnso\Tables\App\Services\Template\Builders;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LaravelEnso\Helpers\App\Classes\Obj;

class Structure
{
    private const DefaultFromConfig = [
        'dtRowId', 'lengthMenu', 'debounce', 'method', 'labels',
        'comparisonOperator', 'responsive', 'searchModes',
    ];

    private const FalseIfMissing = ['selectable',  'preview'];

    private const DefaultFalse = [
        'loading', 'forceInfo', 'searchable', 'sort', 'total', 'date',
        'translatable', 'enum', 'cents', 'money',
    ];

    private const TemplateOrConfigToMeta = ['searchMode', 'fullInfoRecordLimit'];

    private Obj $template;
    private Obj $meta;

    public function __construct(Obj $template, Obj $meta)
    {
        $this->template = $template;
        $this->meta = $meta;
    }

    public function build(): void
    {
        $this->defaults()
            ->defaultFromConfig()
            ->falseIfMissing()
            ->name()
            ->readPath()
            ->length()
            ->templateOrConfigToMeta();
    }

    private function defaults(): self
    {
        $this->meta->set('start', 0);
        $this->meta->set('search', '');

        (new Collection(self::DefaultFalse))
            ->each(fn ($attribute) => $this->meta->set($attribute, false));

        return $this;
    }

    private function defaultFromConfig()
    {
        (new Collection(self::DefaultFromConfig))
            ->each(fn ($attribute) => $this->fromConfigIfNeeded($attribute));

        return $this;
    }

    private function fromConfigIfNeeded($attribute)
    {
        if (! $this->template->has($attribute)) {
            $this->template->set($attribute, config("enso.tables.{$attribute}"));
        }
    }

    private function falseIfMissing()
    {
        (new Collection(self::FalseIfMissing))
            ->each(fn ($attribute) => $this->fillFalseIfMissing($attribute));

        return $this;
    }

    private function fillFalseIfMissing(string $attribute): void
    {
        if (! $this->template->has($attribute)) {
            $this->template->set($attribute, false);
        }
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
            ?? config('enso.tables.dataRouteSuffix');

        $this->template->set('readPath', route("{$prefix}.{$suffix}", [], false));

        return $this;
    }

    private function length(): self
    {
        $this->meta->set(
            'length', $this->template->get('lengthMenu')[0]
        );

        return $this;
    }

    private function templateOrConfigToMeta(): self
    {
        (new Collection(self::TemplateOrConfigToMeta))
            ->each(fn ($attribute) => $this->metaFromTemplateOrConfig($attribute));

        return $this;
    }

    private function metaFromTemplateOrConfig(string $attribute): void
    {
        $value = $this->template->get($attribute)
            ?? config("enso.tables.{$attribute}");

        $this->meta->set($attribute, $value);

        $this->template->forget($attribute);
    }
}
