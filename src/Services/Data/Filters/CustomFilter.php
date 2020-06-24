<?php

namespace LaravelEnso\Tables\Services\Data\Filters;

class CustomFilter extends BaseFilter
{
    public function applies(): bool
    {
        return $this->table->filterApplies($this->config->params());
    }

    public function handle(): void
    {
        $this->table->filter($this->query, $this->config->params());
    }
}
