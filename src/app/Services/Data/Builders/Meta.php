<?php

namespace LaravelEnso\Tables\App\Services\Data\Builders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use LaravelEnso\Tables\App\Contracts\Table;
use LaravelEnso\Tables\App\Exceptions\Cache as Exception;
use LaravelEnso\Tables\App\Services\Data\Config;
use LaravelEnso\Tables\App\Services\Data\Filters;
use ReflectionClass;

class Meta
{
    private Table $table;
    private Config $config;
    private Builder $query;
    private bool $filters;
    private int $count;
    private bool $filtered;
    private array $total;
    private bool $fullRecordInfo;

    public function __construct(Table $table, Config $config)
    {
        $this->table = $table;
        $this->config = $config;
        $this->query = $table->query();
        $this->total = [];
        $this->filters = false;
    }

    public function build(): self
    {
        $this->setCount()
            ->filter()
            ->detailedInfo()
            ->countFiltered()
            ->total();

        return $this;
    }

    public function toArray(): array
    {
        $this->build();

        return [
            'count' => $this->count,
            'filtered' => $this->filtered,
            'total' => $this->total,
            'fullRecordInfo' => $this->fullRecordInfo,
            'filters' => $this->filters,
        ];
    }

    public function count(): int
    {
        return $this->query->count();
    }

    private function setCount(): self
    {
        $this->filtered = $this->count = $this->cachedCount();

        return $this;
    }

    private function filter(): self
    {
        $filters = new Filters(
            $this->table, $this->config, $this->query
        );

        $this->filters = $filters->applies();

        if ($this->filters) {
            $filters->handle();
        }

        return $this;
    }

    private function detailedInfo(): self
    {
        $this->fullRecordInfo = $this->config->meta()->get('forceInfo')
            || $this->count <= $this->config->meta()->get('fullInfoRecordLimit')
            || ! $this->filters;

        return $this;
    }

    private function countFiltered(): self
    {
        if ($this->filters && $this->fullRecordInfo) {
            $this->filtered = $this->count();
        }

        return $this;
    }

    private function total(): self
    {
        if ($this->config->meta()->get('total')) {
            $this->total = (new Total(
                $this->table, $this->config, $this->query
            ))->handle();
        }

        return $this;
    }

    private function cachedCount(): int
    {
        return $this->shouldCache()
            ? Cache::remember($this->cacheKey(), now()->addHour(), fn () => $this->count())
            : $this->count();
    }

    private function cacheKey(): string
    {
        return config('enso.tables.cache.prefix')
            .':'.$this->query->getModel()->getTable();
    }

    private function shouldCache(): bool
    {
        $shouldCache = $this->config->has('countCache')
            ? $this->config->get('countCache')
            : config('enso.tables.cache.count');

        if ($shouldCache) {
            $model = $this->query->getModel();

            if (! (new ReflectionClass($model))->hasMethod('resetTableCache')) {
                throw Exception::missingTrait(get_class($model));
            }
        }

        return $shouldCache;
    }
}
