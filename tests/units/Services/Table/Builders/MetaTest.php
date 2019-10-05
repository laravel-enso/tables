<?php

namespace LaravelEnso\Tables\Tests\units\Services\Table\Builders;

use Config;
use Faker\Factory;
use Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\app\Services\Table\Request;
use LaravelEnso\Tables\app\Services\Table\Builders\Meta;

class MetaTest extends TestCase
{
    private $testModel;
    private $faker;
    private $builder;
    private $params;
    private $select;
    private $fetchMode;

    public function setUp(): void
    {
        parent::setUp();

        // $this->withoutExceptionHandling();

        $this->faker = Factory::create();

        $this->params = ['columns' => [], 'meta' => ['length' => 10]];
        $this->select = 'id, name, is_active, created_at, price';
        $this->fetchMode = false;

        TestModel::createTable();

        $this->testModel = $this->createTestModel();
    }

    /** @test */
    public function can_get_data()
    {
        $response = $this->requestResponse();

        $this->assertEquals(TestModel::count(), $response->get('count'));
    }

    /** @test */
    public function cannot_get_data_cache_count()
    {
        $this->params['cacheCount'] = false;

        $this->requestResponse();

        $this->assertFalse(Cache::has('enso:tables:test_models'));
    }

    /** @test */
    public function can_get_data_with_cache_when_table_cache_trait_used()
    {
        Config::set('enso.tables.cache.count', true);

        $this->requestResponse();

        $this->assertEquals(1, Cache::get('enso:tables:test_models'));
    }

    /** @test */
    public function can_get_data_with_limit()
    {
        $this->params['meta']['length'] = 0;

        $response = $this->requestResponse();

        $this->assertEquals(1, $response->get('filtered'));
        $this->assertEquals(1, $response->get('count'));
    }

    /** @test */
    public function can_get_data_with_total()
    {
        $this->createTestModel();

        $this->params['columns']['price'] = [
            'name' => 'price',
            'data' => 'price',
            'meta' => ['total' => true],
        ];

        $this->params['meta']['total'] = true;

        $response = $this->requestResponse();

        $this->assertEquals(
            TestModel::sum('price'),
            $response->get('total')->get('price')
        );
    }

    /** @test */
    public function can_use_full_info_record_limit()
    {
        $limit = 1;

        $this->createTestModel();

        $this->testModel->update(['name' => 'User']);

        $this->params = [
            'columns' => [
                'name' => [
                    'name' => 'name',
                    'data' => 'name',
                    'meta' => ['searchable' => true]
                ],
            ],
            'meta' => [
                'search' => $this->testModel->name,
                'comparisonOperator' => 'LIKE',
                'fullInfoRecordLimit' => $limit,
                'length' => $limit,
                'searchMode' => 'full',
            ]
        ];

        $response = $this->requestResponse();

        $this->assertFalse($response->get('fullRecordInfo'));
        $this->assertEquals(2, $response->get('count'));
        $this->assertEquals(2, $response->get('filtered'));
    }

    private function requestResponse()
    {
        $this->builder = new Meta(
            new TestTable($this->select),
            new Request($this->params)
        );

        return new Obj($this->builder->data());
    }

    private function createTestModel()
    {
        return TestModel::create([
            'name' => $this->faker->name,
            'is_active' => $this->faker->boolean,
            'price' => $this->faker->numberBetween(1000, 10000),
        ]);
    }
}


