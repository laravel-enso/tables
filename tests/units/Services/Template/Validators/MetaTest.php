<?php

namespace LaravelEnso\Tables\Tests\units\Services\Template\Validators;

use Tests\TestCase;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\app\Exceptions\Meta as Exception;
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

        $this->expectException(Exception::class);

        $this->expectExceptionMessage(Exception::unknownAttributes('wrong_attribute')->getMessage());

        $this->validate();
    }

    /** @test */
    public function cannot_validate_nested_column_with_sortable()
    {
        $this->template->get('columns')->push(new Obj([
            'label' => 'child',
            'name' => 'parent.child',
            'data' => 'parent.child',
            'meta' => ['sortable']
        ]));

        $this->expectException(Exception::class);

        $this->expectExceptionMessage(Exception::unsupported('parent.child')->getMessage());

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
