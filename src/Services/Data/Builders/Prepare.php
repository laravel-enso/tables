<?php

namespace LaravelEnso\Tables\Services\Data\Builders;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use LaravelEnso\Tables\Services\Data\Config;

class Prepare
{
    public function __construct(
        private Config $config,
        private Collection $data,
    ) {
    }

    public function handle(): Collection
    {
        $this->strip()
            ->flatten();

        return $this->data;
    }

    private function strip(): self
    {
        if (! $this->config->filled('strip')) {
            return $this;
        }

        $this->data->transform(function ($row) {
            foreach ($this->config->get('strip')->toArray() as $attr) {
                unset($row[$attr]);
            }

            return $row;
        });

        return $this;
    }

    private function flatten(): void
    {
        if ($this->config->get('flatten')) {
            $this->data->transform(fn ($record) => Arr::dot($record));
        }
    }
}
