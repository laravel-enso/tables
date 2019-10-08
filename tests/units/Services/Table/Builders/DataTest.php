<?php

namespace LaravelEnso\Tables\Tests\units\Services\Table\Builders;

use Faker\Factory;
use Tests\TestCase;
use Illuminate\Support\Facades\Route;
use LaravelEnso\Enums\app\Services\Enum;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\app\Services\Template;
use LaravelEnso\Tables\app\Services\Table\Request;
use LaravelEnso\Tables\app\Services\Table\Builders\Data;

class DataTest extends TestCase
{
    private $testModel;
    private $faker;
    private $table;
    private $template;
    private $request;

    public function setUp(): void
    {
        parent::setUp();

        // $this->withoutExceptionHandling();

        $this->faker = Factory::create();

        Route::any('route')->name('testTables.tableData');
        Route::getRoutes()->refreshNameLookups();

        $this->request = new Request(['columns' => [], 'meta' => ['length' => 10]]);

        TestModel::createTable();

        $this->testModel = $this->createTestModel();

        $this->table = (new TestTable())->select(
            'id, name, is_active, created_at, price, color'
        );

        $this->template = (new Template($this->table))->load([
            'template' => new Obj([
                'routePrefix' => 'testTables',
                'buttons' => [],
                'columns' => []
            ]),
            'meta' => new Obj(),
        ]);
    }

    /** @test */
    public function can_get_data()
    {
        $response = $this->requestResponse();

        $this->assertCount(TestModel::count(), $response);

        $this->assertTrue(
            $response->first()
                ->diff($this->testModel->toArray())
                ->isEmpty()
        );
    }

    /** @test */
    public function can_get_data_with_appends()
    {
        $this->template->put('appends', new Obj(['custom']));

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
        $this->request->put('flatten', true);

        $this->template->put('appends', new Obj(['custom']));

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

        $this->template->meta()->set('enum', true);

        $this->template->columns()->push(new Obj([
            'name' => 'color',
            'data' => 'color',
            'enum' => BuilderTestEnum::class,
            'meta' => []
        ]));

        $response = $this->requestResponse();

        $this->assertEquals(
            BuilderTestEnum::get($this->testModel->color),
            $response->first()->get('color')
        );
    }

    /** @test */
    public function can_get_data_with_date()
    {
        $this->template->meta()->set('date', true);

        $this->template->columns()->push(new Obj([
            'name' => 'created_at',
            'dateFormat' => 'Y-m-d',
            'meta' => ['date'],
        ]));

        $response = $this->requestResponse();

        $this->assertEquals(
            $this->testModel->created_at->format('Y-m-d'),
            $response->first()->get('created_at')
        );
    }

    /** @test */
    public function can_get_data_with_cents()
    {
        $this->template->meta()->set('cents', true);

        $this->template->columns()->push(new Obj([
            'name' => 'price',
            'data' => 'price',
            'meta' => ['cents']
        ]));

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

        $this->request->columns()->push(new Obj([
            'name' => 'id',
            'data' => 'id',
            'meta' => ['sortable' => true, 'sort' => 'DESC'],
        ]));

        $this->request->get('meta')->put('sort', true);

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

        $this->request->columns()->push(new Obj([
            'name' => 'name',
            'data' => 'name',
            'meta' => ['sortable' => true, 'sort' => 'ASC', 'nullLast' => true],
        ]));

        $this->request->get('meta')->put('sort', true);

        $response = $this->requestResponse();

        $this->assertEquals(
            $secondModel->name,
            $response->first()->get('name')
        );
    }

    /** @test */
    public function can_get_data_with_limit()
    {
        $this->request->get('meta')->set('length', 0);

        $response = $this->requestResponse();

        $this->assertCount(0, $response);
    }

    /** @test */
    public function can_use_full_info_record_limit()
    {
        $limit = 1;

        $this->createTestModel();

        $this->testModel->update(['name' => 'User']);

        $this->request->columns()->push(new Obj([
            'name' => 'name',
            'data' => 'name',
            'meta' => ['searchable' => true]
        ]));

        $this->template->set('comparisonOperator', 'LIKE');

        $this->request->put('meta', new Obj([
            'search' => $this->testModel->name,
            'fullInfoRecordLimit' => $limit,
            'length' => $limit,
            'searchMode' => 'full',
        ]));

        $response = $this->requestResponse();

        $this->assertCount(1, $response);
    }

    private function requestResponse()
    {
        $builder = new Data(
            $this->table, $this->request, $this->template
        );

        return new Obj($builder->data());
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
}

class BuilderTestEnum extends Enum
{
    public const Red = 1;
    public const Blue = 2;
}
