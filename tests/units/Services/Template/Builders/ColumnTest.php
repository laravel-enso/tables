<?php

namespace Services\Template\Builders;

use Tests\TestCase;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\app\Services\Template\Builders\Columns;

class ColumnTest extends TestCase
{
    private $meta;

    private $template;

    protected function setUp() :void
    {
        parent::setUp();

        $this->meta = new Obj([]);
    }



    /** @test */
    public function can_build_with_default_meta()
    {
        $this->template = $this->basicTemplate([
            [

            ]
        ]);

        $this->build();

        $this->assertTrue($this->template['columns'][0]->has('meta'));
    }

    /** @test */
    public function can_build_with_sort()
    {
        $this->template = $this->basicTemplate([
            [
                'meta'=>[
                    'sort:ASC'
                ]
            ]
        ]);

        $this->build();

        $this->assertEquals('ASC',$this->template['columns'][0]['meta']['sort']);
        $this->assertEquals('ASC',$this->meta['sort']);
    }


    private function basicTemplate($columns = [])
    {
        $template = [
            'columns' => $columns,
        ];
        return new Obj($template);
    }

    private function build(): void
    {
        $structure = new Columns(
            $this->template,
            $this->meta
        );

        $structure->build();
    }

}
