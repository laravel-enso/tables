<?php

namespace LaravelEnso\Tables\Services\Data\Builders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\Tables\Contracts\RawTotal;
use LaravelEnso\Tables\Contracts\Table;
use LaravelEnso\Tables\Exceptions\Meta as Exception;
use LaravelEnso\Tables\Services\Data\Computors\Number;
use LaravelEnso\Tables\Services\Data\Config;

class Total
{
    private array $total;

    public function __construct(
        private Table $table,
        private Config $config,
        private Builder $query
    ) {
        $this->total = [];
    }

    public function handle(): array
    {
        $columns = $this->config->columns()
            ->filter(fn ($column) => $column->get('meta')->get('total')
                || $column->get('meta')->get('rawTotal')
                || $column->get('meta')->get('average'));

        $aggregates = (new Collection($columns->all()))
            ->map(fn ($column) => $this->aggregate($column))
            ->filter()
            ->values();

        $result = $aggregates->isNotEmpty()
            ? $this->query->getQuery()->cloneWithoutBindings(['select'])
                ->select($aggregates->all())->first()
            : null;

        $columns->each(fn ($column) => $this->compute($column, $result));

        return $this->total;
    }

    private function aggregate(Obj $column)
    {
        if ($column->get('meta')->get('rawTotal')) {
            return $this->rawTotal($column);
        }

        $function = $column->get('meta')->get('average') ? 'AVG' : 'SUM';

        return DB::raw(
            "{$function}({$column->get('data')}) as {$column->get('name')}"
        );
    }

    private function compute(Obj $column, ?object $result): void
    {
        $name = $column->get('name');

        $this->total[$name] ??= $result?->{$name} ?? 0;

        if ($column->get('meta')->get('cents')) {
            $this->total[$name] /= 100;
        }

        if ($column->has('number')) {
            $this->total[$name] = Number::format(
                $this->total[$name],
                $column->get('number')
            );
        }
    }

    private function rawTotal(Obj $column)
    {
        if (! $this->table instanceof RawTotal) {
            throw Exception::missingInterface();
        }

        $rawTotal = $this->table->rawTotal($column);

        if (is_numeric($rawTotal)) {
            $this->total[$column->get('name')] = $rawTotal;

            return;
        }

        return DB::raw("{$rawTotal} as {$column->get('name')}");
    }
}
