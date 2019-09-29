<?php

namespace LaravelEnso\Tables\app\Services\Table\Filters;

use Illuminate\Database\Eloquent\Builder;
use LaravelEnso\Tables\app\Contracts\Table;
use LaravelEnso\Tables\app\Services\Table\Request;
use LaravelEnso\Tables\app\Contracts\CustomFilter as TableCustomFilter;

class CustomFilter
{
    private $request;
    private $query;
    private $filters;
    private $table;
    public function __construct(Request $request, Builder $query, Table $table)
    {
        $this->table = $table;
        $this->request = $request;
        $this->query = $query;
        $this->filters = false;
    }

    public function handle()
    {
        $this->customFilter()
            ->checkParams();

        return $this->filters;
    }

    private function customFilter()
    {
        if($this->table instanceof TableCustomFilter) {
            $this->query = $this->table->filter($this->query);
            $this->filters = true;
        }

        return $this;
    }

    private function checkParams()
    {
        if ($this->request->filled('params')) {
            $this->filters = true;
        }
    }
}
