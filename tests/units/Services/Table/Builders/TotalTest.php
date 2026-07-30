<?php

namespace LaravelEnso\Tables\Tests\units\Services\Table\Builders;

use Illuminate\Support\Facades\DB;
use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\Tables\Contracts\RawTotal;
use LaravelEnso\Tables\Services\Data\Builders\Total;
use LaravelEnso\Tables\Tests\units\Services\SetUp;
use LaravelEnso\Tables\Tests\units\Services\TestModel;
use LaravelEnso\Tables\Tests\units\Services\TestTable;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TotalTest extends TestCase
{
    use SetUp;

    #[Test]
    public function computes_aggregates_in_a_single_query()
    {
        $model = $this->createTestModel();
        $expectedPrice = $this->testModel->price + $model->price;

        $this->config->columns()->push(
            new Obj([
                'name' => 'price',
                'data' => 'price',
                'meta' => ['total' => true],
            ]),
            new Obj([
                'name' => 'average_price',
                'data' => 'price',
                'meta' => ['average' => true],
            ]),
            new Obj([
                'name' => 'double_price',
                'data' => 'price',
                'meta' => ['rawTotal' => true],
            ]),
        );

        $table = new class extends TestTable implements RawTotal {
            public function rawTotal(Obj $column): string
            {
                return 'SUM(price * 2)';
            }
        };

        DB::flushQueryLog();
        DB::enableQueryLog();

        $total = (new Total($table, $this->config, $table->query()))->handle();

        $this->assertCount(1, DB::getQueryLog());
        $this->assertSame($expectedPrice, $total['price']);
        $this->assertEquals($expectedPrice / 2, $total['average_price']);
        $this->assertSame($expectedPrice * 2, $total['double_price']);
    }

    #[Test]
    public function uses_numeric_raw_total_without_an_additional_query()
    {
        $expected = (string) TestModel::sum('price');

        $this->config->columns()->push(new Obj([
            'name' => 'price',
            'data' => 'price',
            'meta' => ['rawTotal' => true],
        ]));

        $table = new class extends TestTable implements RawTotal {
            public function rawTotal(Obj $column): string
            {
                return (string) TestModel::sum('price');
            }
        };

        DB::flushQueryLog();
        DB::enableQueryLog();

        $total = (new Total($table, $this->config, $table->query()))->handle();

        $this->assertCount(1, DB::getQueryLog());
        $this->assertSame($expected, $total['price']);
    }
}
