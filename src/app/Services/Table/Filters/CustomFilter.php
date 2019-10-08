<?php

namespace LaravelEnso\Tables\app\Services\Table\Filters;

use Illuminate\Database\Eloquent\Builder;
use LaravelEnso\Tables\app\Services\Table\Request;
use LaravelEnso\Tables\app\Contracts\CustomFilter as CustomFilterTable;

class CustomFilter extends BaseFilter
{
    private $table;

    public function __construct(Request $request, Builder $query, CustomFilterTable $table)
    {
        parent::__construct($request, $query);

        $this->table = $table;
    }

    public function handle(): bool
    {
        if ($this->request->filled('params')) {
            $this->filter();
        }

        return $this->filters;
    }

    private function filter()
    {
        $this->query = $this->table->filter($this->query);
        $this->filters = true;
    }
}
