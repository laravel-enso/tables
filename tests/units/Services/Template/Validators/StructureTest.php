<?php

namespace LaravelEnso\Tables\Tests\units\Services\Template\Validators;

use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\Tables\Exceptions\Template as Exception;
use LaravelEnso\Tables\Services\Template\Validators\Structure\Structure;
use Tests\TestCase;

class StructureTest extends TestCase
{
    private $validator;
    private $template;

    protected function setUp(): void
    {
        parent::setUp();

        $this->template = new Obj($this->mockedTemplate());
    }

    /** @test */
    public function cannot_validate_without_mandatory_attribute()
    {
        $this->template->forget('routePrefix');

        $this->expectException(Exception::class);

        $this->expectExceptionMessage(Exception::missingAttributes('routePrefix')->getMessage());

        $this->validate();
    }

    /** @test */
    public function cannot_validate_with_invalid_attribute()
    {
        $this->template->set('invalid_attributes', 'invalid');

        $this->expectException(Exception::class);

        $this->expectExceptionMessage(Exception::unknownAttributes('invalid_attributes')->getMessage());

        $this->validate();
    }

    /** @test */
    public function can_validate()
    {
        $this->validate();

        $this->assertTrue(true);
    }

    private function validate()
    {
        $this->validator = new Structure($this->template);

        $this->validator->validate();
    }

    private function mockedTemplate()
    {
        return new Obj([
            'lengthMenu' => new Obj([]),
            'debounce' => 10,
            'method' => 'POST',
            'selectable' => true,
            'comparisonOperator' => 'LIKE',
            'name' => 'name',
            'columns' => [],
            'buttons' => [],
            'routePrefix' => 'prefix',
        ]);
    }
}
