<?php

namespace LaravelEnso\Tables\app\Services\Table;

use Illuminate\Support\Facades\Cache;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\app\Exceptions\QueryException;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use LaravelEnso\Tables\app\Services\Table\Computors\Date;
use LaravelEnso\Tables\app\Services\Table\Computors\Enum;
use LaravelEnso\Tables\app\Services\Table\Computors\OptimalChunk;
use LaravelEnso\Tables\app\Services\Table\Computors\Translatable;

class Builder
{
    private $request;
    private $query;
    private $filters;
    private $count;
    private $filtered;
    private $total;
    private $data;
    private $columns;
    private $meta;
    private $fullRecordInfo;
    private $statics;
    private $fetchMode;

    public function __construct(Obj $request, QueryBuilder $query)
    {
        $this->request = $request;
        $this->computeMeta();
        $this->meta = $this->request->get('meta');
        $this->computeColumns();
        $this->columns = $this->request->get('columns');
        $this->query = $query;
        $this->total = collect();
        $this->statics = false;
        $this->fetchMode = false;
        $this->filters = false;
    }

    public function fetcher()
    {
        $this->meta->set(
            'length', OptimalChunk::get($this->count())
        );

        return $this;
    }

    public function fetch($page = 0)
    {
        $this->fetchMode = true;

        $this->meta->set(
            'start',
            $this->meta->get('length') * $page
        );

        $this->run();

        return $this->data;
    }

    public function data()
    {
        $this->run();

        $this->checkActions();

        return [
            'count' => $this->count,
            'filtered' => $this->filtered,
            'total' => $this->total,
            'data' => $this->data,
            'fullRecordInfo' => $this->fullRecordInfo,
            'filters' => $this->filters,
        ];
    }

    private function run()
    {
        $this->initStatics()
            ->setCount()
            ->filter()
            ->setDetailedInfo()
            ->countFiltered()
            ->sort()
            ->setTotal()
            ->limit()
            ->setData();

        if ($this->data->isNotEmpty()) {
            $this->setAppends()
                ->collect()
                ->computeEnum()
                ->computeDate()
                ->computeTranslatable()
                ->flatten();
        }
    }

    private function checkActions()
    {
        if (count($this->data) === 0) {
            return;
        }

        if (! isset($this->data[0]['dtRowId'])) {
            throw new QueryException(__(
                'You have to add in the main query \'id as "dtRowId"\' for the actions to work'
            ));
        }
    }

    private function setCount()
    {
        if (! $this->fetchMode) {
            $this->filtered = $this->count = $this->cachedCount();
        }

        return $this;
    }

    private function filter()
    {
        $this->filters = (new Filters(
            $this->request,
            $this->query,
            $this->columns
        ))->handle();

        return $this;
    }

    private function setDetailedInfo()
    {
        $this->fullRecordInfo = $this->meta->get('forceInfo')
            || (! $this->fetchMode && (! $this->filters
                || $this->count <= $this->meta->get('fullInfoRecordLimit')));

        return $this;
    }

    private function countFiltered()
    {
        if ($this->filters && $this->fullRecordInfo) {
            $this->filtered = $this->count();
        }

        return $this;
    }

    private function sort()
    {
        if (! $this->meta->get('sort')) {
            return $this;
        }

        $this->columns->each(function ($column) {
            if ($column->get('meta')->get('sortable') && $column->get('meta')->get('sort')) {
                $column->get('meta')->get('nullLast')
                    ? $this->query->orderByRaw($this->rawSort($column))
                    : $this->query->orderBy(
                        $column->get('data'),
                        $column->get('meta')->get('sort')
                    );
            }
        });

        return $this;
    }

    private function rawSort($column)
    {
        return "({$column->get('data')} IS NULL),"
            ."{$column->get('data')} {$column->get('meta')->get('sort')}";
    }

    private function setTotal()
    {
        if (! $this->meta->get('total') || ! $this->fullRecordInfo || $this->fetchMode) {
            return $this;
        }

        $this->total = $this->columns
            ->reduce(function ($total, $column) {
                if ($column->get('meta')->get('total')) {
                    $total[$column->get('name')] = $this->query->sum($column->get('data'));
                }

                return $total;
            }, []);

        return $this;
    }

    private function limit()
    {
        $this->query->skip($this->meta->get('start'))
            ->take($this->meta->get('length'));

        return $this;
    }

    private function setData()
    {
        $this->data = $this->query->get();

        return $this;
    }

    private function setAppends()
    {
        if (! $this->request->has('appends')) {
            return $this;
        }

        $this->data->each->setAppends(
            $this->request->get('appends')->toArray()
        );

        return $this;
    }

    private function collect()
    {
        $this->data = collect($this->data->toArray());

        return $this;
    }

    private function initStatics()
    {
        if ($this->statics) {
            return $this;
        }

        if ($this->meta->get('enum')) {
            Enum::columns($this->columns);
        }

        if ($this->meta->get('date')) {
            Date::columns($this->columns);
        }

        if ($this->fetchMode && $this->meta->get('translatable')) {
            Translatable::columns($this->columns);
        }

        $this->statics = true;

        return $this;
    }

    private function computeEnum()
    {
        if ($this->meta->get('enum')) {
            $this->data = $this->data->map(function ($row) {
                return Enum::compute($row, $this->columns);
            });
        }

        return $this;
    }

    private function computeDate()
    {
        if ($this->meta->get('date')) {
            $this->data = $this->data->map(function ($row) {
                return Date::compute($row, $this->columns);
            });
        }

        return $this;
    }

    private function computeTranslatable()
    {
        if ($this->fetchMode && $this->meta->get('translatable')) {
            $this->data = $this->data->map(function ($row) {
                return Translatable::compute($row);
            });
        }

        return $this;
    }

    private function flatten()
    {
        if (! $this->request->get('flatten')) {
            return;
        }

        $this->data = collect($this->data)
            ->map(function ($record) {
                return array_dot($record);
            });
    }

    private function computeMeta()
    {
        $this->request->set(
            'meta',
            new Obj($this->array($this->request->get('meta')))
        );
    }

    private function computeColumns()
    {
        $this->request->set(
            'columns',
            $this->request->get('columns')
                ->map(function ($column) {
                    return new Obj($this->array($column));
                })
        );
    }

    private function cachedCount()
    {
        if (! $this->request->get('cache')) {
            return $this->count();
        }

        $cacheKey = 'table:'.$this->query->getModel()->getTable();

        if (! Cache::has($cacheKey)) {
            Cache::put($cacheKey, $this->count(), now()->addHours(1));
        }

        return (int) Cache::get($cacheKey);
    }

    private function count()
    {
        return $this->query->count();
    }

    private function array($arg)
    {
        return is_string($arg)
            ? json_decode($arg, true)
            : $arg;
    }
}
