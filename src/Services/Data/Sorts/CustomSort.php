<?php

namespace LaravelEnso\Tables\Services\Data\Sorts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Config as ConfigFacade;
use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\Tables\Services\Data\Config;

class CustomSort
{
    public function __construct(
        private Config $config,
        private Builder $query
    ) {
    }

    public function applies(): bool
    {
        return $this->config->meta()->get('sort')
            && $this->columns()->isNotEmpty();
    }

    public function handle(): void
    {
        $this->columns()
            ->each(fn ($column) => $this->query->when(
                $column->get('meta')->get('nullLast'),
                fn ($query) => $query->orderByRaw($this->rawSort($column)),
                fn ($query) => $query
                    ->orderBy($column->get('data'), $column->get('meta')->get('sort'))
            ));

        $template = $this->config->template();

        $sort = ConfigFacade::get('enso.tables.dtRowId') === $template->get('dtRowId')
            ? "{$template->get('table')}.{$template->get('dtRowId')}"
            : $template->get('dtRowId');

        $this->query->orderBy($sort, $template->get('defaultSortDirection'));
    }

    private function rawSort($column): string
    {
        $data = $column->get('data');
        $sort = $column->get('meta')->get('sort');

        return "({$data} IS NULL), {$data} {$sort}";
    }

    protected function columns(): Obj
    {
        return $this->config->columns()->filter(fn ($column) => $column
            ->get('meta')->get('sortable') && $column->get('meta')->get('sort'));
    }
}
