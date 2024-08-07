<?php

namespace LaravelEnso\Tables\Services\Data\Sorts;

use Illuminate\Database\Eloquent\Builder;
use LaravelEnso\Tables\Services\Data\Config;

class Sort
{
    public function __construct(
        private Config $config,
        private Builder $query
    ) {
    }

    public function handle(): void
    {
        $sort = new CustomSort($this->config, $this->query);

        if ($sort->applies()) {
            $sort->handle();
        } elseif (!$this->query->getQuery()->orders) {
            $column = $this->config->template()->get('defaultSort');
            $direction = $this->config->template()->get('defaultSortDirection');

            $this->query->orderBy($column, $direction);
        }
    }
}
