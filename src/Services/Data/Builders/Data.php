<?php

namespace LaravelEnso\Tables\Services\Data\Builders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\Tables\Contracts\ConditionalActions;
use LaravelEnso\Tables\Contracts\CustomCssClasses;
use LaravelEnso\Tables\Contracts\Table;
use LaravelEnso\Tables\Services\Data\Config;
use LaravelEnso\Tables\Services\Data\Filters;
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

    public function handle(): Collection
    {
        $this->filter()
            ->sort()
            ->limit()
            ->setData();

        if ($this->data->isNotEmpty()) {
            $this->data = (new Computor($this->config, $this->data))->handle();

            if (! $this->fetchMode) {
                $this->actions();
                $this->style();
            }
        }

        return $this->data;
    }

    public function toArray(): array
    {
        return ['data' => $this->handle()];
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

    private function actions(): void
    {
        if ($this->table instanceof ConditionalActions) {
            $this->data->transform(fn ($row) => $row + [
                '_actions' => $this->rowActions($row),
            ]);
        }
    }

    private function style(): void
    {
        if ($this->table instanceof CustomCssClasses) {
            $this->data->transform(fn ($row) => $row + [
                '_cssClasses' => $this->table->cssClasses($row),
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
