<?php

namespace LaravelEnso\Tables\Tests\units\Services\Template\Validators;

use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\Tables\Exceptions\Template as Exception;
use LaravelEnso\Tables\Services\Template\Validators\Structure\Attributes;
use Tests\TestCase;

class AttributesTest extends TestCase
{
    private $validator;
    private $template;

    protected function setUp(): void
    {
        parent::setUp();

        $this->template = new Obj($this->mockedTemplate());
    }

    /** @test */
    public function cannot_validate_with_invalid_length_menu_format()
    {
        $this->template->set('lengthMenu', 'string');

        $this->expectException(Exception::class);

        $this->expectExceptionMessage(Exception::invalidLengthMenu()->getMessage());

        $this->validate();
    }

    /** @test */
    public function cannot_validate_with_non_numeric_debounce()
    {
        $this->template->set('debounce', 'string');

        $this->expectException(Exception::class);

        $this->expectExceptionMessage(Exception::invalidDebounce()->getMessage());

        $this->validate();
    }

    /** @test */
    public function cannot_validate_with_invalid_method()
    {
        $this->template->set('method', 'patch');

        $this->expectException(Exception::class);

        $this->expectExceptionMessage(Exception::invalidMethod()->getMessage());

        $this->validate();
    }

    /** @test */
    public function cannot_validate_with_non_boolean_selectable()
    {
        $this->template->set('selectable', 'string');

        $this->expectException(Exception::class);

        $this->expectExceptionMessage(Exception::invalidSelectable()->getMessage());

        $this->validate();
    }

    /** @test */
    public function cannot_validate_with_invalid_comparison_operator()
    {
        $this->template->set('comparisonOperator', 'I_DONT_LIKE');

        $this->expectException(Exception::class);

        $this->expectExceptionMessage(Exception::invalidComparisonOperator()->getMessage());

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
        $this->validator = new Attributes($this->template);

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
