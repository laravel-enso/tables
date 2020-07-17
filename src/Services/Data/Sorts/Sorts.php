<?php

namespace LaravelEnso\Tables\Services\Data\Sorts;

use Illuminate\Database\Eloquent\Builder;
use LaravelEnso\Tables\Services\Data\Config;

class Sorts
{
    private Config $config;
    private Builder $query;

    public function __construct(Config $config, Builder $query)
    {
        $this->config = $config;
        $this->query = $query;
    }

    public function handle(): void
    {
        $sort = new Sort($this->config, $this->query);

        if ($sort->applies()) {
            $sort->handle();

            return;
        }

        (new DefaultSort($this->config, $this->query))->handle();
    }
}
