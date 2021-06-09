<?php

namespace LaravelEnso\Tables\Services\Data\Builders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\Tables\Contracts\ConditionalActions;
use LaravelEnso\Tables\Contracts\Table;
use LaravelEnso\Tables\Services\Data\ArrayComputors;
use LaravelEnso\Tables\Services\Data\Config;
use LaravelEnso\Tables\Services\Data\Filters;
use LaravelEnso\Tables\Services\Data\ModelComputors;
use LaravelEnso\Tables\Services\Data\Sorts\Sort;

class Data
{
    private Builder $query;
    private Collection $data;

    public function __construct(
        private Table $table,
        private Config $config,
        private bool $fetchMode = false
    ) {
        $this->query = $table->query();
    }

    public function build(): Collection
    {
        $this->filter()
            ->sort()
            ->limit()
            ->setData();

        if ($this->data->isNotEmpty()) {
            $this->appends()
                ->modelCompute()
                ->sanitize()
                ->arrayCompute()
                ->strip()
                ->flatten();

            if (! $this->fetchMode) {
                $this->actions();
            }
        }

        return $this->data;
    }

    public function toArray(): array
    {
        return ['data' => $this->build()];
    }

    private function filter(): self
    {
        (new Filters($this->table, $this->config, $this->query))->handle();

        return $this;
    }

    private function sort(): self
    {
        (new Sort($this->config, $this->query))->handle();

        return $this;
    }

    private function limit(): self
    {
        $this->query->skip($this->config->meta()->get('start'))
            ->take($this->config->meta()->get('length'));

        return $this;
    }

    private function setData(): self
    {
        $this->data = $this->query->get();

        return $this;
    }

    private function appends(): self
    {
        if ($this->config->filled('appends')) {
            $this->data->each->setAppends(
                $this->config->get('appends')->toArray()
            );
        }

        return $this;
    }

    private function modelCompute(): self
    {
        $this->data = ModelComputors::handle($this->config, $this->data);

        return $this;
    }

    private function sanitize(): self
    {
        $this->data = new Collection($this->data->toArray());

        return $this;
    }

    private function arrayCompute(): self
    {
        $this->data = ArrayComputors::handle($this->config, $this->data);

        return $this;
    }

    private function strip(): self
    {
        if (! $this->config->filled('strip')) {
            return $this;
        }

        $this->data = $this->data->map(function ($row) {
            foreach ($this->config->get('strip')->toArray() as $attr) {
                unset($row[$attr]);
            }

            return $row;
        });

        return $this;
    }

    private function flatten(): void
    {
        if ($this->config->get('flatten')) {
            $this->data = $this->data
                ->map(fn ($record) => Arr::dot($record));
        }
    }

    private function actions(): void
    {
        if ($this->table instanceof ConditionalActions) {
            $this->data = $this->data->map(fn ($row) => $row + [
                '_actions' => $this->rowActions($row),
            ]);
        }
    }

    private function rowActions(array $row): array
    {
        return $this->config->template()->buttons()->get('row')
            ->map(fn (Obj $action) => $action->get('name'))
            ->filter(fn (string $action) => $this->table->render($row, $action))
            ->values()
            ->toArray();
    }
}
