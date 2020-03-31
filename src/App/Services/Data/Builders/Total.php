<?php

namespace LaravelEnso\Tables\App\Services\Data\Builders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use LaravelEnso\Helpers\App\Classes\Obj;
use LaravelEnso\Tables\App\Contracts\RawTotal;
use LaravelEnso\Tables\App\Contracts\Table;
use LaravelEnso\Tables\App\Exceptions\Meta as Exception;
use LaravelEnso\Tables\App\Services\Data\Config;

class Total
{
    private Table $table;
    private Config $config;
    private Builder $query;
    private array $total;

    public function __construct(Table $table, Config $config, Builder $query)
    {
        $this->table = $table;
        $this->config = $config;
        $this->query = $query;
        $this->total = [];
    }

    public function handle(): array
    {
        $this->config->columns()
            ->filter(fn ($column) => $column->get('meta')->get('total')
                || $column->get('meta')->get('rawTotal')
                || $column->get('meta')->get('average'))
            ->each(fn ($column) => $this->compute($column));

        return $this->total;
    }

    private function compute(Obj $column): void
    {
        if ($column->get('meta')->get('rawTotal')) {
            $this->total[$column->get('name')] = $this->rawTotal($column);
        } elseif ($column->get('meta')->get('average')) {
            $this->total[$column->get('name')] = $this->query->average($column->get('data'));
        } else {
            $this->total[$column->get('name')] = $this->query->sum($column->get('data'));
        }

        if ($column->get('meta')->get('cents')) {
            $this->total[$column->get('name')] /= 100;
        }
    }

    private function rawTotal($column): float
    {
        if (! $this->table instanceof RawTotal) {
            throw Exception::missingInterface();
        }

        return optional(
            (clone $this->query)->select(
                DB::raw("{$this->table->rawTotal($column)} as {$column->get('name')}")
            )->first()
        )->{$column->get('name')} ?? 0;
    }
}
