<?php

namespace LaravelEnso\Tables\Tests\units\Services\Template\Validators;

use Illuminate\Support\Collection;
use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\Tables\Attributes\Column as Attributes;
use LaravelEnso\Tables\Exceptions\Meta as Exception;
use LaravelEnso\Tables\Services\Template\Validators\Columns\Columns;
use Tests\TestCase;

class MetaTest extends TestCase
{
    private $validator;
    private $template;

    protected function setUp(): void
    {
        parent::setUp();

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
    public function cannot_validate_meta_with_invalid_attributes()
    {
        $this->template->get('columns')->first()->set('meta', new Obj(['invalid_attribute']));

        $this->expectException(Exception::class);

        $this->expectExceptionMessage(Exception::unknownAttributes('invalid_attribute')->getMessage());

        $this->validate();
    }

    /** @test */
    public function cannot_validate_nested_column_with_sortable()
    {
        $this->template->get('columns')->push(new Obj([
            'label' => 'child',
            'name' => 'parent.child',
            'data' => 'parent.child',
            'meta' => ['sortable'],
        ]));

        $this->expectException(Exception::class);

        $this->expectExceptionMessage(Exception::unsupported('parent.child')->getMessage());

        $this->validate();
    }

    private function mockedColumn()
    {
        return Collection::wrap(Attributes::Mandatory)
            ->flip()->map(fn () => new Obj());
    }

    private function validate()
    {
        $this->validator = new Columns($this->template);

        $this->validator->validate();
    }
}
