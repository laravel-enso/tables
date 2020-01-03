<?php

namespace LaravelEnso\Tables\Tests\units\Services\Template\Builders;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LaravelEnso\Helpers\App\Classes\Obj;
use LaravelEnso\Tables\App\Attributes\Column;
use LaravelEnso\Tables\App\Services\Template\Builders\Columns;
use Tests\TestCase;

class ColumnTest extends TestCase
{
    private $meta;
    private $template;

    protected function setUp() :void
    {
        parent::setUp();

        // $this->withoutExceptionHandling();

        $this->meta = new Obj([]);
        $this->template = new Obj(['columns' => [[]]]);
    }

    /** @test */
    public function can_build_basic()
    {
        $this->build();

        $this->assertTrue($this->template->get('columns')->first()->has('meta'));
    }

    /** @test */
    public function can_build_with_meta_attributes()
    {
        (new Collection(Column::Meta))->each(function($attribute) {
            $expected = Str::startsWith($attribute, 'sort:')
                ? ['key' => 'sort', 'value' => Str::replaceFirst('sort:', '', $attribute)]
                : ['key' => $attribute, 'value' => true];

            $this->assertPresent($attribute, $expected);
        });
    }

    private function assertPresent(string $attribute, $expected)
    {
        $this->template->set('columns', new Obj([['meta' => [$attribute]]]));
            $this->build();

            $this->assertEquals(
                $expected['value'],
                $this->template->get('columns')->first()->get('meta')->get($expected['key'])
            );
    }

    private function build(): void
    {
        (new Columns(
            $this->template,
            $this->meta
        ))->build();
    }
}
