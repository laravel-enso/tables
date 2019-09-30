<?php

namespace LaravelEnso\Tables\app\Services\Table\Builders;

use Illuminate\Support\Facades\Cache;
use LaravelEnso\Tables\app\Contracts\Table;
use LaravelEnso\Tables\app\Contracts\RawTotal;
use LaravelEnso\Tables\app\Services\Table\Request;
use LaravelEnso\Tables\app\Services\Table\Filters\Filters;

class Meta
{
    private $table;
    private $request;
    private $query;
    private $filters;
    private $count;
    private $filtered;
    private $total;
    private $fullRecordInfo;

    public function __construct(Table $table, Request $request)
    {
        $this->table = $table;
        $this->request = $request;
        $this->query = $table->query();
        $this->total = collect();
        $this->filters = false;
    }

    public function data()
    {
        $this->setCount()
            ->filter()
            ->setDetailedInfo()
            ->countFiltered()
            ->setTotal();

        return [
            'count' => $this->count,
            'filtered' => $this->filtered,
            'total' => $this->total,
            'fullRecordInfo' => $this->fullRecordInfo,
            'filters' => $this->filters,
        ];
    }

    private function setCount()
    {
        $this->filtered = $this->count = $this->cachedCount();

        return $this;
    }

    private function filter()
    {
        $this->filters = (new Filters())->filter(
            $this->request,
            $this->query,
            $this->table
        );

        return $this;
    }

    private function setDetailedInfo()
    {
        $this->fullRecordInfo = $this->request->meta()->get('forceInfo')
            || $this->count <= $this->request->meta()->get('fullInfoRecordLimit')
            || ! $this->filters;

        return $this;
    }

    private function countFiltered()
    {
        if ($this->filters && $this->fullRecordInfo) {
            $this->filtered = $this->count();
        }

        return $this;
    }

    private function setTotal()
    {
        $this->request->columns()
            ->filter(function ($column) {
                return $column->get('meta')->get('total');
            })
            ->each(function ($column) {
                $this->total[$column->get('name')] = $this->table instanceof RawTotal
                    ? $this->table->rawTotal($column)
                    : $this->query->sum($column->get('data'));

                if ($column->get('meta')->get('cents')) {
                    $this->total[$column->get('name')] /= 100;
                }
            });

        return $this;
    }

    private function cachedCount()
    {
        if (! json_decode($this->request->get('cache'))) {
            return $this->count();
        }

        $cacheKey = config('enso.tables.cache_prefix')
            .':'.$this->query->getModel()->getTable();

        return Cache::remember($cacheKey, now()->addHour(), function () {
            return $this->count();
        });
    }

    public function count()
    {
        return $this->query->count();
    }
}
