<?php

namespace Services\Table;

use App;
use Faker\Factory;
use Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Helpers\app\Classes\Enum;
use LaravelEnso\Tables\app\Services\Table\Builder;
use LaravelEnso\Tables\app\Exceptions\QueryException;

class BuilderTest extends TestCase
{

    private $testModel;

    private $faker;

    private $builder;

    public function setUp(): void
    {
        parent::setUp();

        // $this->withoutExceptionHandling();

        $this->faker = Factory::create();

        $this->createTestModelTable();

        $this->testModel = $this->createTestModel();
        $this->createTestModel();
    }

    /** @test */
    public function can_get_data()
    {
        $response = $this->requestResponse();

        $this->assertCount(BuilderTestModel::count(), $response["data"]);
        $this->assertEquals(BuilderTestModel::count(), $response["count"]);

        $this->assertTrue(
            $response["data"]->pluck('name')
                ->contains($this->testModel->name)
        );
    }

    /** @test */
    public function cannot_get_data_without_dtRowId()
    {
        $this->expectException(QueryException::class);

        $response = $this->requestResponse([], 'id');
    }

    /** @test */
    public function can_get_data_with_appends()
    {
        $response = $this->requestResponse([
            "appends" => [
                "append"
            ]
        ]);

        $this->assertEquals('appended', $response["data"][0]["append"]['append']);
    }

    /** @test */
    public function can_get_data_with_flatten()
    {
        $response = $this->requestResponse([
            'flatten' => true,
            "appends" => [
                "append"
            ]
        ]);

        $this->assertEquals('appended', $response["data"][0]["append.append"]);
    }

    /** @test */
    public function can_get_data_with_enum()
    {
        $this->testModel->is_active = 1;
        $this->testModel->save();

        $response = $this->requestResponse([
            "columns" => [
                'is_active' => ['name' => 'is_active', 'enum' => BuilderTestEnum::class]
            ],
            'meta' => [
                'enum' => true
            ]
        ]);

        $this->assertEquals('Active', $response["data"][0]["is_active"]);
    }

    /** @test */
    public function can_get_data_with_date()
    {
        $response = $this->requestResponse([
            "columns" => [
                'created_at' => ['name' => 'created_at', 'dateFormat' => 'Y-m-d', 'meta' => ['date' => true]]
            ],
            'meta' => [
                'date' => true
            ]
        ]);

        $this->assertEquals($this->testModel->created_at->format('Y-m-d'), $response["data"][0]["created_at"]);
    }

    /** @test */
    public function can_get_data_with_cent()
    {
        $response = $this->requestResponse([
            "columns" => [
                'price' => ['name' => 'price', 'meta' => ['cents' => true]]
            ],
            'meta' => [
                'cents' => true
            ]
        ]);

        $this->assertEquals(
            BuilderTestModel::find($response["data"][0]["dtRowId"])->price / 100.0,
            $response["data"][0]["price"]
        );
    }

    /** @test */
    public function can_get_data_with_translatable()
    {
        App::setLocale('test');
        app('translator')->setLoaded([
            '*' => [
                'test' => [
                    'test' => [
                        'test' => 'this is test'
                    ]
                ]
            ]
        ]);
        $this->testModel->name = 'test.test';
        $this->testModel->save();

        $response = $this->requestResponse([
            "columns" => [
                'name' => ['name' => 'name', 'meta' => ['translatable' => true]]
            ],
            'meta' => [
                'translatable' => true
            ]
        ], 'id as dtRowId,name', true);

        $this->assertEquals('this is test', $response["data"][0]['name']);
    }

    /** @test */
    public function can_get_data_with_sort()
    {
        $response = $this->requestResponse([
            "columns" => [
                "id" => ["data" => "id", "meta" => ["sortable" => true, "sort" => "DESC"]]
            ],
            'meta' => [
                'sort' => true,
            ]
        ]);

        $this->assertEquals(
            BuilderTestModel::orderBy("id", "desc")->first()->id,
            $response["data"][0]["dtRowId"]
        );
    }

    /** @test */
    public function can_get_data_with_sort_null_last()
    {
        $this->testModel->name = null;
        $this->testModel->save();

        $response = $this->requestResponse([
            "columns" => [
                "name" => ["data" => "name", "meta" => ["sortable" => true, "sort" => "ASC", "nullLast" => true]]
            ],
            'meta' => [
                'sort' => true,
            ]
        ]);

        $this->assertNotNull($response["data"][0]["name"]);
    }

    /** @test */
    public function can_get_data_with_cache()
    {
        Cache::shouldReceive('get')
            ->andReturn(-10)
            ->shouldReceive('has')
            ->andReturn(true);

        $response = $this->requestResponse([
            'cache' => true
        ]);

        $this->assertEquals(-10, $response["count"]);
    }

    /** @test */
    public function can_get_data_with_limit()
    {
        $response = $this->requestResponse([
            'meta' => [
                'length' => 0,
            ]
        ]);

        $this->assertCount(0, $response["data"]);
    }


    /** @test */
    public function can_get_data_with_total()
    {
        $response = $this->requestResponse([
            "columns" => [
                "id" => ['name' => 'id', "data" => "id", "meta" => ['total' => true]]
            ],
            'meta' => [
                'total' => true,
            ]
        ]);

        $this->assertEquals(
            BuilderTestModel::all()->sum("id"),
            $response["total"]['id']
        );
    }

    private function requestResponse(array $params = [], $select = 'id as dtRowId,name,is_active,created_at,price', $withFetch = false)
    {
        $params['columns'] = $params['columns'] ?? [];
        $params['meta'] = $params['meta'] ?? [];
        $params['meta']['length'] = $params['meta']['length'] ?? BuilderTestModel::count();

        $this->builder = new Builder(
            new Obj($params),
            BuilderTestModel::selectRaw($select)
        );

        if ($withFetch)
            $this->builder->fetch();

        return collect($this->builder->data());
    }

    private function createTestModel()
    {
        return BuilderTestModel::create([
            'name' => $this->faker->name,
            'is_active' => $this->faker->boolean,
            'price' => $this->faker->numberBetween(1000, 10000),
        ]);
    }

    private function createTestModelTable()
    {
        Schema::create('builder_test_models', function ($table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->boolean('is_active')->nullable();
            $table->integer('price')->nullable();
            $table->timestamps();
        });
    }

}


class BuilderTestModel extends Model
{
    protected $fillable = ['name', 'is_active', 'price'];

    public function getAppendAttribute()
    {
        return [
            'append' => 'appended'
        ];
    }
}

class BuilderTestEnum extends Enum
{
    public const Active = 1;
    public const DeActive = 0;
}
