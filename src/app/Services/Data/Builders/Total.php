<?php

namespace LaravelEnso\Tables\app\Services\Data\Builders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use LaravelEnso\Tables\app\Contracts\RawTotal;
use LaravelEnso\Tables\app\Contracts\Table;
use LaravelEnso\Tables\app\Exceptions\MetaException;
use LaravelEnso\Tables\app\Services\Data\Config;

class Total
{
    private $table;
    private $config;
    private $query;
    private $total;

    public function __construct(Table $table, Config $config, Builder $query)
    {
        $this->table = $table;
        $this->config = $config;
        $this->query = $query;
        $this->total = [];
    }

    public function handle()
    {
        $this->compute();

        return $this->total;
    }

    private function compute()
    {
        $this->config->columns()
            ->filter(function ($column) {
                return $column->get('meta')->get('total')
                    || $column->get('meta')->get('rawTotal');
            })->each(function ($column) {
                $this->total[$column->get('name')] = $column->get('meta')->get('rawTotal')
                    ? $this->rawTotal($column)
                    : $this->query->sum($column->get('data'));

                if ($column->get('meta')->get('cents')) {
                    $this->total[$column->get('name')] /= 100;
                }
            });
    }

    private function rawTotal($column)
    {
        if (! $this->table instanceof RawTotal) {
            throw MetaException::missingInterface();
        }

        return optional(
            (clone $this->query)->select(
                DB::raw("{$this->table->rawTotal($column)} as {$column->get('name')}")
            )->first()
        )->{$column->get('name')} ?? 0;
    }
}
