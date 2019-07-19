<?php

namespace Services\Table;

use Faker\Factory;
use Tests\TestCase;
use LaravelEnso\Tables\app\Services\Table\Filters;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use LaravelEnso\Helpers\app\Classes\Obj;

class FilterTest extends TestCase
{

    private $testModel;

    private $faker;

    private $filter;

    public function setUp(): void
    {
        parent::setUp();

        // $this->withoutExceptionHandling();

        $this->faker = Factory::create();

        $this->createTestModelTable();
        $this->createRelationModelTable();

        $this->testModel = $this->createTestModel();
        $this->createRelationModel();

        $this->createTestModel();
    }

    /** @test */
    public function can_get_data_without_condition()
    {
        $result = $this->requestResponse([
            "columns" => [
                "name" => ["data" => "name"]
            ]
        ]);

        $this->assertCount(FilterTestModel::count(), $result);

        $this->assertTrue(
            $result->pluck('name')
                ->contains($this->testModel->name)
        );
    }

    /** @test */
    public function can_get_data_with_search()
    {
        $this->testModel->name = 'TEST_NAME';
        $this->testModel->save();

        $result = $this->requestResponse([
            "columns" => [
                "name" => ["data" => "name", 'searchable' => true, 'meta' => ['searchable' => true]],
            ],
            'meta' => [
                'search' => $this->testModel->name,
                'comparisonOperator'=>'LIKE'
            ]
        ]);
        $this->assertCount(1, $result);

        $this->assertEquals(
            $result[0]['name'],
            $this->testModel->name
        );
    }

    /** @test */
    public function can_get_data_with_interval()
    {
        $this->testModel->created_at='2012-12-12';
        $this->testModel->save();

        $result = $this->requestResponse([
            'intervals'=>[
                'filter_test_models' => [
                    'created_at' => [
                        'dbDateFormat'=>'Y-m-d',
                        'dateFormat'=>'d-m-Y',
                        "min"=>'12-12-2012',
                        "max"=>'13-12-2012',
                    ]
                ]
            ]
        ]);

        $this->assertCount(1, $result);

        $this->assertEquals(
            $result[0]['name'],
            $this->testModel->name
        );
    }

    /** @test */
    public function can_get_data_with_filter()
    {
        $result = $this->requestResponse([
            'filters'=>[
                'filter_test_models' => [
                    'name' => $this->testModel->name
                ]
            ]
        ]);

        $this->assertCount(1, $result);

        $this->assertEquals(
            $result[0]['name'],
            $this->testModel->name
        );
    }

    /** @test */
    public function can_get_data_with_relation_search()
    {
        $result = $this->requestResponse([
            "columns" => [
                "name" => ['name'=>'relation.name',"data" => "relation.name", 'searchable' => true, 'meta' => ['searchable' => true]],
            ],
            'meta' => [
                'search' => $this->testModel->relation->name,
                'comparisonOperator'=>'LIKE'
            ]
        ]);

        $this->assertCount(1, $result);

        $this->assertEquals(
            $result[0]['name'],
            $this->testModel->name
        );
    }


    private function requestResponse(array $params = [], $select = 'id as dtRowId,name,created_at')
    {
        $params['columns'] = $params['columns'] ?? [];
        $params['meta'] = $params['meta'] ?? [];

        $query = FilterTestModel::select('*');

        $this->filter = new Filters(
            new Obj($params),
            $query,
            new Obj($params["columns"])
        );

        $this->filter->handle();

        return $query->get();
    }

    private function createTestModel()
    {
        return FilterTestModel::create([
            'name' => $this->faker->word,
        ]);
    }
    private function createRelationModel()
    {
        return FilterRelationModel::create([
            'name' => $this->faker->word,
            'parent_id' => $this->testModel->id,
        ]);
    }

    private function createTestModelTable()
    {
        Schema::create('filter_test_models', function ($table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->timestamps();
        });
    }

    private function createRelationModelTable()
    {
        Schema::create('filter_relation_models', function ($table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->integer('parent_id')->nullable();
            $table->foreign('parent_id')->references('id')->on('filter_test_models');
            $table->timestamps();
        });
    }

}


class FilterTestModel extends Model
{
    protected $fillable = ['name'];

    public function relation()
    {
        return $this->hasOne(FilterRelationModel::class, 'parent_id');
    }
}

class FilterRelationModel extends Model
{
    protected $fillable = ['name', 'parent_id'];
}

