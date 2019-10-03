<?php

namespace LaravelEnso\Tables\app\Services\Table\Builders\Filters;


class CustomFilter extends BaseFilter
{
    public function handle(): bool
    {
        $this->filter();

        return $this->filters;
    }

    private function filter()
    {
        if ($this->request->filled('params')) {
            $this->query = $this->table->filter(
                $this->query, $this->request
            );

            $this->filters = true;
        }
    }
}
