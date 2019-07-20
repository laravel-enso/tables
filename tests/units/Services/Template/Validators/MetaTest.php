<?php

namespace Services\Template\Validators;

use Tests\TestCase;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\app\Exceptions\TemplateException;
use LaravelEnso\Tables\app\Attributes\Column as Attributes;
use LaravelEnso\Tables\app\Services\Template\Validators\Columns;

class MetaTest extends TestCase
{
    private $validator;
    private $template;

    protected function setUp() :void
    {
        parent::setUp();

        // $this->withoutExceptionHandling();

        $this->template = new Obj(['columns' => [$this->mockedColumn()]]);
    }

    /** @test */
    public function can_validate_meta()
    {
        $this->template->get('columns')->first()->set('meta', new Obj(['sortable']));

        $this->validate();

        $this->assertTrue(true);
    }

    /** @test */
    public function cannot_validate_meta_with_wrong_attributes()
    {
        $this->template->get('columns')->first()->set('meta', new Obj(['wrong_attribute']));

        $this->expectException(TemplateException::class);

        $this->expectExceptionMessage('Unknown Meta Parameter(s): "wrong_attribute"');

        $this->validate();
    }

    /** @test */
    public function cannot_validate_nested_column_with_sortable()
    {
        $this->template->get('columns')->push(new Obj([
            'label' => 'child',
            'name' => 'parent.child',
            'data' => 'parent.child',
            'meta' => [
                'sortable'
            ]
        ]));

        $this->expectException(TemplateException::class);

        $this->expectExceptionMessage('Nested columns do not support "sortable": "parent.child"');

        $this->validate();
    }


    private function mockedColumn()
    {
        return collect(Attributes::Mandatory)->flip()->map(function () {
            return new Obj([]);
        });
    }

    private function validate()
    {
        $this->validator = new Columns($this->template);

        $this->validator->validate();
    }
}
