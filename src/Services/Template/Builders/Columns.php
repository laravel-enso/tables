<?php

namespace LaravelEnso\Tables\Services\Template\Builders;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\Tables\Attributes\Column as Attributes;

class Columns
{
    private const FromColumn = ['enum', 'number', 'resource'];

    private const FromMeta = [
        'cents', 'date', 'datetime', 'filterable',
        'method', 'searchable', 'translatable',
    ];

    public function __construct(
        private Obj $template,
        private Obj $meta
    ) {
    }

    public function build(): void
    {
        $this->template->set('columns', $this->columns());
    }

    private function columns()
    {
        return $this->template->get('columns')
            ->reduce(fn ($columns, $column) => $columns
                ->push($this->compute($column)), new Obj());
    }

    private function compute($column): Obj
    {
        $this->meta($column)
            ->enum($column)
            ->number($column)
            ->sort($column)
            ->total($column)
            ->visibility($column)
            ->defaults($column);

        return $column;
    }

    private function meta($column): self
    {
        $meta = Collection::wrap(Attributes::Meta)
            ->reduce(fn ($meta, $attribute) => $meta
                ->set($attribute, $column->get('meta')?->contains($attribute)), new Obj());

        $meta->set('visible', true)
            ->set('hidden', false);

        $column->set('meta', $meta);

        return $this;
    }

    private function enum($column): self
    {
        if (!$column->has('enum')) {
            return $this;
        }

        if (enum_exists($column->get('enum'))) {
            $column->set('enum', Collection::wrap($column->get('enum')::cases())
                ->mapWithKeys(fn ($value) => [$value->value => $value->name]))
                ->toArray();
        } else {
            $this->setLegacyEnum($column);
        }

        return $this;
    }

    private function setLegacyEnum($column): void
    {
        $enum = App::make($column->get('enum'));
        $enum::localisation(false);
        $column->set('enum', $enum::all());
        $enum::localisation(true);
    }

    private function number($column): self
    {
        if ($column->has('number')) {
            $number = $column->get('number');
            $number->set('symbol', $number->get('symbol', ''));
            $number->set('precision', $number->get('precision', 0));
            $number->set('template', $number->get('template', '%s%v'));
        }

        return $this;
    }

    private function sort($column): self
    {
        $meta = $column->get('meta');

        $templateSort = $this->templateSort($meta);

        $meta->set('sort', $templateSort);

        $meta->forget(['sort:ASC', 'sort:DESC']);

        if ($templateSort) {
            $this->meta->set('sort', true);
        }

        return $this;
    }

    private function templateSort($meta): ?string
    {
        if ($meta->get('sort:ASC')) {
            return 'ASC';
        }

        if ($meta->get('sort:DESC')) {
            return 'DESC';
        }

        return $meta->get('sort');
    }

    private function total($column): self
    {
        $meta = $column->get('meta');

        $hasTotal = $meta->get('total') || $meta->get('rawTotal')
            || $meta->get('customTotal') || $meta->get('average');

        if ($hasTotal) {
            $this->meta->set('total', true);
        }

        return $this;
    }

    private function visibility($column): self
    {
        if ($column->get('meta')->get('notVisible')) {
            $column->get('meta')->set('visible', false);
            $column->get('meta')->forget('notVisible');
        }

        return $this;
    }

    private function defaults($column): void
    {
        $this->fromColumn($column)
            ->merge($this->fromColumnMeta($column))
            ->each(fn ($attribute) => $this->meta->set($attribute, true));
    }

    private function fromColumn($column): Collection
    {
        return Collection::wrap(self::FromColumn)
            ->filter(fn ($attribute) => $column->get($attribute));
    }

    private function fromColumnMeta($column): Collection
    {
        return Collection::wrap(self::FromMeta)
            ->filter(fn ($attribute) => $column->get('meta')->get($attribute));
    }
}
