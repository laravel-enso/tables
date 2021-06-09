<?php

namespace LaravelEnso\Tables\Tests\units\Services\Template\Validators;

use Illuminate\Support\Collection;
use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\Tables\Attributes\Column as Attributes;
use LaravelEnso\Tables\Exceptions\Column as ColumnException;
use LaravelEnso\Tables\Exceptions\Meta as MetaException;
use LaravelEnso\Tables\Services\Template\Validators\Columns\Columns;
use Tests\TestCase;

class ColumnTest extends TestCase
{
    private $validator;
    private $template;

    protected function setUp(): void
    {
        parent::setUp();

        $this->template = new Obj(['columns' => [$this->mockedColumn()]]);
    }

    /** @test */
    public function can_validate()
    {
        $this->validate();

        $this->assertTrue(true);
    }

    /** @test */
    public function cannot_validate_with_missing_mandatory_attribute()
    {
        $this->template->get('columns')->first()->forget('label');

        $this->expectException(ColumnException::class);

        $this->expectExceptionMessage(ColumnException::missingAttributes('label')->getMessage());

        $this->validate();
    }

    /** @test */
    public function cannot_validate_with_invalid_attribute()
    {
        $this->template->get('columns')->first()->set('invalid_attribute', 'invalid');

        $this->expectException(ColumnException::class);

        $this->expectExceptionMessage(ColumnException::unknownAttributes('invalid_attribute')->getMessage());

        $this->validate();
    }

    /** @test */
    public function cannot_validate_with_invalid_enum()
    {
        $this->template->get('columns')->first()->set('enum', 'MissingEnum');

        $this->expectException(ColumnException::class);

        $this->expectExceptionMessage(ColumnException::enumNotFound('MissingEnum')->getMessage());

        $this->validate();
    }

    /** @test */
    public function cannot_validate_with_invalid_resource()
    {
        $this->template->get('columns')->first()->set('resource', 'MissingResource');

        $this->expectException(ColumnException::class);

        $this->expectExceptionMessage(ColumnException::resourceNotFound('MissingResource')->getMessage());

        $this->validate();
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

        $this->expectException(MetaException::class);

        $this->expectExceptionMessage(MetaException::unknownAttributes('invalid_attribute')->getMessage());

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
