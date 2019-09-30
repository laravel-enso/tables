<?php

namespace Services\Table;

use App;
use Faker\Factory;
use Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use LaravelEnso\Enums\app\Services\Enum;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\app\Services\Table\Builder;

class BuilderTest extends TestCase
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
        $this->select = 'id, name, is_active, created_at, price, color';
        $this->fetchMode = false;

        $this->createTestModelTable();

        $this->testModel = $this->createTestModel();
    }

    /** @test */
    public function can_get_data()
    {
        $response = $this->requestResponse();

        $this->assertCount(BuilderTestModel::count(), $response->get('data'));
        $this->assertEquals(BuilderTestModel::count(), $response->get('count'));

        $this->assertTrue(
            $response->get('data')->first()
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
            $response->get('data')->first()
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
            $response->get('data')->first()->get('custom.relation')
        );
    }

    /** @test */
    public function can_get_data_with_enum()
    {
        $this->testModel->update(['color' => 2]);

        $this->params['columns']['color'] = [
            'name' => 'color',
            'enum' => BuilderTestEnum::class
        ];

        $this->params['meta']['enum'] = 2;

        $response = $this->requestResponse();

        $this->assertEquals(
            BuilderTestEnum::get($this->testModel->color),
            $response->get('data')->first()->get('color')
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
            $response->get('data')->first()->get('created_at')
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
            $response->get('data')->first()->get('price')
        );
    }

    /** @test */
    public function can_get_data_with_translatable()
    {
        App::make('translator')->addJsonPath(__DIR__.'/lang');

        App::setLocale('lang');

        $this->testModel->update(['name' => 'should translate']);

        $this->params['columns']['name'] = [
            'name' => 'name',
            'meta' => ['translatable' => true],
        ];

        $this->params['meta']['translatable'] = true;

        $this->fetchMode = true;

        $response = $this->requestResponse();

        $this->assertEquals('translation', $response['data'][0]['name']);
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
            BuilderTestModel::orderByDesc('id')->first()->id,
            $response->get('data')->first()->get('id')
        );
    }

    /** @test */
    public function can_get_data_with_sort_null_last()
    {
        $this->secondModel = $this->createTestModel();

        $this->testModel->update(['name' => null]);

        $this->params['columns']['name'] = [
            'data' => 'name',
            'meta' => ['sortable' => true, 'sort' => 'ASC', 'nullLast' => true],
        ];

        $this->params['meta']['sort'] = true;

        $response = $this->requestResponse();

        $this->assertEquals(
            $this->secondModel->name,
            $response->get('data')->first()->get('name')
        );
    }

    /** @test */
    public function can_get_data_with_cache()
    {
        Cache::shouldReceive('get')
            ->andReturn(12)
            ->shouldReceive('has')
            ->andReturn(true);

        $this->params['cache'] = true;

        $response = $this->requestResponse();

        $this->assertEquals(12, $response->get('count'));
    }

    /** @test */
    public function can_get_data_with_limit()
    {
        $this->params['meta']['length'] = 0;

        $response = $this->requestResponse();

        $this->assertCount(0, $response['data']);
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
            BuilderTestModel::sum('price'),
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
        $this->assertCount(1, $response->get('data'));
        $this->assertEquals(2, $response->get('count'));
        $this->assertEquals(2, $response->get('filtered'));
    }

    private function requestResponse()
    {
        $this->builder = new Builder(
            new Obj($this->params),
            BuilderTestModel::selectRaw($this->select),
            BuilderTestModel::selectRaw($this->select)
        );

        if ($this->fetchMode) {
            $this->builder->fetch();
        }

        return new Obj($this->builder->data());
    }

    private function createTestModel()
    {
        return BuilderTestModel::create([
            'name' => $this->faker->name,
            'color' => BuilderTestEnum::keys()->random(),
            'is_active' => $this->faker->boolean,
            'price' => $this->faker->numberBetween(1000, 10000),
        ]);
    }

    private function createTestModelTable()
    {
        Schema::create('builder_test_models', function ($table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->tinyInteger('color')->nullable();
            $table->boolean('is_active')->nullable();
            $table->integer('price')->nullable();
            $table->timestamps();
        });
    }
}

class BuilderTestModel extends Model
{
    protected $fillable = ['name', 'price', 'color', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function getCustomAttribute()
    {
        return [
            'relation' => 'name'
        ];
    }
}

class BuilderTestEnum extends Enum
{
    public const Red = 1;
    public const Green = 2;
}
