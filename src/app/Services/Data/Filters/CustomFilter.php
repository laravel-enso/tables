<?php

namespace LaravelEnso\Tables\app\Services\Data\Filters;

use LaravelEnso\Helpers\app\Classes\Obj;

class CustomFilter extends BaseFilter
{
    public function applies(): bool
    {
        return $this->table->filterApplies($this->config->params());
    }

    public function handle()
    {
        $this->table->filter($this->query, $this->config->params());
    }
}
