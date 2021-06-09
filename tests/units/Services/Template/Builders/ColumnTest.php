<?php

namespace LaravelEnso\Tables\Tests\units\Services\Template\Builders;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\Tables\Attributes\Column;
use LaravelEnso\Tables\Services\Template\Builders\Columns;
use Tests\TestCase;

class ColumnTest extends TestCase
{
    private $meta;
    private $template;

    protected function setUp() :void
    {
        parent::setUp();

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
        Collection::wrap(Column::Meta)
            ->each(fn ($attribute) => $this->assertPresent($attribute, $this->metaValue($attribute)));
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

    protected function metaValue($attribute): array
    {
        if ($attribute === 'notVisible') {
            return ['key' => 'visible', 'value' => false];
        }

        if (Str::startsWith($attribute, 'sort:')) {
            return ['key' => 'sort', 'value' => Str::replaceFirst('sort:', '', $attribute)];
        }

        return ['key' => $attribute, 'value' => true];
    }
}
