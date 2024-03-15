<?php

namespace LaravelEnso\Tables\Services\Data\Builders;

use Carbon\Carbon;
use Illuminate\Cache\TaggableStore;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config as ConfigFacade;
use LaravelEnso\Tables\Contracts\CustomCount;
use LaravelEnso\Tables\Contracts\CustomCountCacheKey;
use LaravelEnso\Tables\Contracts\CustomFilteredCount;
use LaravelEnso\Tables\Contracts\Table;
use LaravelEnso\Tables\Exceptions\Cache as Exception;
use LaravelEnso\Tables\Services\Data\Config;
use LaravelEnso\Tables\Services\Data\Filters;
use ReflectionClass;

class Meta
{
    private Builder $query;
    private bool $filters;
    private int $count;
    private int $filtered;
    private array $total;
    private bool $fullRecordInfo;

    public function __construct(
        private Table $table,
        private Config $config
    ) {
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
            'count'             => $this->count,
            'formattedCount'    => number_format($this->count),
            'filtered'          => $this->filtered,
            'formattedFiltered' => number_format($this->filtered),
            'total'             => $this->total,
            'fullRecordInfo'    => $this->fullRecordInfo,
            'filters'           => $this->filters,
            'pagination'        => $this->pagination()->toArray(),
        ];
    }

    public function count($filtered = false): int
    {
        if ($this->table instanceof CustomCount && !$filtered) {
            return $this->table->count();
        }

        if ($this->table instanceof CustomFilteredCount && $filtered) {
            return $this->table->filteredCount();
        }

        return $this->query
            ->applyScopes()
            ->getQuery()
            ->getCountForPagination();
    }

    public function filter(): self
    {
        $filters = new Filters($this->table, $this->config, $this->query);

        $this->filters = $filters->applies();

        if ($this->filters) {
            $filters->handle();
        }

        return $this;
    }

    private function setCount(): self
    {
        $this->filtered = $this->count = $this->cachedCount();

        return $this;
    }

    private function detailedInfo(): self
    {
        $this->fullRecordInfo = $this->config->meta()->get('forceInfo')
            || $this->count <= $this->config->meta()->get('fullInfoRecordLimit')
            || (!$this->filters && !$this->config->meta()->get('total'));

        return $this;
    }

    private function countFiltered(): self
    {
        if ($this->filters && $this->fullRecordInfo) {
            $this->filtered = $this->count(true);
        }

        return $this;
    }

    private function total(): self
    {
        if ($this->fullRecordInfo && $this->config->meta()->get('total')) {
            $this->total = (new Total($this->table, $this->config, $this->query))
                ->handle();
        }

        return $this;
    }

    private function pagination(): Pagination
    {
        $args = [$this->config->meta(), $this->filtered, $this->fullRecordInfo];

        return new Pagination(...$args);
    }

    private function cachedCount(): int
    {
        if (!$this->shouldCache()) {
            return $this->count();
        }

        $cacheKey = $this->table instanceof CustomCountCacheKey
            ? $this->cacheKey($this->table->countCacheKey())
            : $this->cacheKey();

        if (!$this->cache($this->cacheKey())->has($cacheKey)) {
            $this->cache($this->cacheKey())
                ->put($cacheKey, $this->count(), Carbon::now()->addHour());
        }

        return $this->cache($this->cacheKey())->get($cacheKey);
    }

    private function cache(string $tag)
    {
        return Cache::getStore() instanceof TaggableStore
            ? Cache::tags($tag)
            : Cache::store();
    }

    private function cacheKey(?string $suffix = null): string
    {
        $model = $this->query->getModel();
        $key = (new $model())->tableCacheKey();

        return Collection::wrap([$key, $suffix])->filter()->implode(':');
    }

    private function shouldCache(): bool
    {
        $shouldCache = $this->config->has('countCache')
            ? $this->config->get('countCache')
            : ConfigFacade::get('enso.tables.cache.count');

        if ($shouldCache) {
            $model = $this->query->getModel();

            $instance = (new ReflectionClass($model));

            $compatible = $instance->hasMethod('resetTableCache')
                && $instance->hasMethod('tableCacheKey');

            if (!$compatible) {
                throw Exception::missingTrait($model::class);
            }
        }

        return $shouldCache
            && (Cache::getStore() instanceof TaggableStore
                || !$this->table instanceof CustomCountCacheKey);
    }
}
