<?php

namespace LaravelEnso\Tables\Tests\units\Services;

use Faker\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\ParallelTesting;
use Illuminate\Support\Facades\Route;
use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\Tables\Services\Data\Config;
use LaravelEnso\Tables\Services\Data\FilterAggregator;
use LaravelEnso\Tables\Services\Data\Request;
use LaravelEnso\Tables\Services\Template;

trait SetUp
{
    private $faker;
    private $testModel;
    private $table;
    private $config;
    private $query;

    protected function setUp(): void
    {
        parent::setUp();

        if (ParallelTesting::token()) {
            $this->setUpParralelTesting();
        }

        $this->faker = Factory::create();

        Route::any('route')->name('testTables.tableData');
        Route::getRoutes()->refreshNameLookups();

        TestModel::createTable();

        $this->testModel = $this->createTestModel();

        $columns = $internalFilters = $filters = $intervals = $params = [];

        $meta = ['length' => 10, 'search' => '', 'searchMode' => 'full'];
        $filters = [$internalFilters, $filters, $intervals, $params];

        $aggregator = new FilterAggregator(...$filters);

        $request = new Request($columns, $meta, $aggregator());

        $request->columns()->push(new Obj([
            'name' => 'name',
            'data' => 'name',
            'meta' => ['searchable' => true],
        ]));

        $this->table = new TestTable();

        $template = (new Template($this->table))->buildCacheable()
            ->buildNonCacheable();

        $this->config = new Config($request, $template);

        $this->query = $this->table->query();
    }

    protected function createTestModel($name = null)
    {
        return TestModel::create([
            'name'  => $name ?? $this->faker->name,
            'price' => $this->faker->numberBetween(1000, 10000),
        ]);
    }

    protected function tearDown(): void
    {
        $token = ParallelTesting::token();

        if ($token) {
            File::delete(Cache::get("table_{$token}_template"));

            Cache::forget("table_{$token}_template");
        }

        parent::tearDown();
    }

    private function setUpParralelTesting()
    {
        $token = ParallelTesting::token();

        $base = base_path('vendor/laravel-enso/tables/tests/units/Services/templates');

        $path = "{$base}/template_{$token}.json";

        $template = new Collection(json_decode(File::get("{$base}/template.json"), true));

        File::put($path, $template->toJson(JSON_PRETTY_PRINT));

        Cache::put("table_{$token}_template", $path);
    }
}
