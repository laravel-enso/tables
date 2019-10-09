<?php

namespace LaravelEnso\Tables\Tests\units\Services\Table\Builders;

use Faker\Factory;
use Tests\TestCase;
use LaravelEnso\Enums\app\Services\Enum;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\app\Services\Template;
use LaravelEnso\Tables\app\Services\Table\Config;
use LaravelEnso\Tables\app\Services\Table\Builders\Data;

class DataTest extends TestCase
{
    private $testModel;
    private $faker;
    private $builder;
    private $params;
    private $select;

    public function setUp(): void
    {
        parent::setUp();

        // $this->withoutExceptionHandling();

        $this->faker = Factory::create();

        $this->params = ['columns' => [], 'meta' => ['length' => 10]];
        $this->select = 'id, name, is_active, created_at, price, color';

        TestModel::createTable();

        $this->testModel = $this->createTestModel();
    }

    /** @test */
    public function can_get_data()
    {
        $response = $this->requestResponse();

        $this->assertCount(TestModel::count(), $response);
        \Log::info($response->first());
        \Log::info($this->testModel->toArray());
        $this->assertTrue(
            $response->first()
                ->diff($this->testModel->toArray())
                ->isEmpty()
        );
    }

    /** @test */
    public function can_get_data_with_appends()
    {
        $this->params['appends'] = ['custom'];

        $response = $this->requestResponse();

        $this->assertEquals(
            'name',
            $response->first()
                ->get('custom')
                ->get('relation')
        );
    }

    /** @test */
    public function can_get_data_with_flatten()
    {
        $this->params['flatten'] = true;
        $this->params['appends'] = ['custom'];

        $response = $this->requestResponse();

        $this->assertEquals(
            'name',
            $response->first()->get('custom.relation')
        );
    }

    /** @test */
    public function can_get_data_with_enum()
    {
        $this->testModel->update(['color' => BuilderTestEnum::Blue]);

        $this->params['columns']['color'] = [
            'name' => 'color',
            'enum' => BuilderTestEnum::class,
            'meta' => []
        ];

        $this->params['meta']['enum'] = true;

        $response = $this->requestResponse();

        $this->assertEquals(
            BuilderTestEnum::get($this->testModel->color),
            $response->first()->get('color')
        );
    }

    /** @test */
    public function can_get_data_with_date()
    {
        $this->params['columns']['created_at'] = [
            'name' => 'created_at',
            'dateFormat' => 'Y-m-d',
            'meta' => ['date' => true],
        ];

        $this->params['meta']['date'] = true;

        $response = $this->requestResponse();

        $this->assertEquals(
            $this->testModel->created_at->format('Y-m-d'),
            $response->first()->get('created_at')
        );
    }

    /** @test */
    public function can_get_data_with_cent()
    {
        $this->params['columns']['price'] = [
            'name' => 'price',
            'meta' => ['cents' => true]
        ];

        $this->params['meta']['cents'] = true;

        $response = $this->requestResponse();

        $this->assertEquals(
            $this->testModel->price / 100,
            $response->first()->get('price')
        );
    }

    /** @test */
    public function can_get_data_with_sort()
    {
        $this->createTestModel();

        $this->params['columns']['id'] = [
            'data' => 'id',
            'meta' => ['sortable' => true, 'sort' => 'DESC'],
        ];

        $this->params['meta']['sort'] = true;

        $response = $this->requestResponse();

        $this->assertEquals(
            TestModel::orderByDesc('id')->first()->id,
            $response->first()->get('id')
        );
    }

    /** @test */
    public function can_get_data_with_sort_null_last()
    {
        $secondModel = $this->createTestModel();

        $this->testModel->update(['name' => null]);

        $this->params['columns']['name'] = [
            'data' => 'name',
            'meta' => ['sortable' => true, 'sort' => 'ASC', 'nullLast' => true],
        ];

        $this->params['meta']['sort'] = true;

        $response = $this->requestResponse();

        $this->assertEquals(
            $secondModel->name,
            $response->first()->get('name')
        );
    }

    /** @test */
    public function can_get_data_with_limit()
    {
        $this->params['meta']['length'] = 0;

        $response = $this->requestResponse();

        $this->assertCount(0, $response);
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

        $this->assertCount(1, $response);
    }

    private function requestResponse()
    {
        $this->builder = new Data(
            (new Config(collect($this->params)->forget(['flatten','appends'])->toArray()))->setTemplate($this->template())
        );

        return new Obj($this->builder->data());
    }

    private function createTestModel()
    {
        return TestModel::create([
            'name' => $this->faker->name,
            'is_active' => $this->faker->boolean,
            'price' => $this->faker->numberBetween(1000, 10000),
            'color' => BuilderTestEnum::Red,
        ]);
    }

    private function template()
    {
        return (new Template(new TestTable($this->select)))->load([
            'template' => new Obj($this->params),
            'meta' => new Obj($this->params['meta']),
        ]);
    }
}

class BuilderTestEnum extends Enum
{
    public const Red = 1;
    public const Blue = 2;
}
