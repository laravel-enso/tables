<?php

namespace LaravelEnso\Tables\app\Services\Table\Filters;

use Illuminate\Database\Eloquent\Builder;
use LaravelEnso\Tables\app\Services\Table\Request;
use LaravelEnso\Tables\app\Contracts\CustomFilter as TableCustomFilter;

class CustomFilter extends BaseFilter
{
    private $custom;

    public function __construct(Request $request, Builder $query, TableCustomFilter $custom)
    {
        parent::__construct($request, $query);

        $this->custom = $custom;
    }

    public function handle(): bool
    {
        $this->filter();

        return $this->filters;
    }

    private function filter()
    {
        if ($this->request->filled('params')) {
            $this->query = $this->custom->filter(
                $this->query, $this->request
            );

            $this->filters = true;
        }
    }
}
