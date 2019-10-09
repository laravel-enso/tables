<?php

namespace LaravelEnso\Tables\app\Services\Table\Filters;

class CustomFilter extends BaseFilter
{
    public function handle(): bool
    {
        $this->filter();

        return $this->filters;
    }

    private function filter()
    {
        if ($this->config->filled('params')) {
            $this->query = $this->config->table()->filter(
                $this->query, $this->config
            );

            $this->filters = true;
        }
    }
}
