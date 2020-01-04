<?php

namespace LaravelEnso\Tables\Tests\units\Services\Template\Validators;

use LaravelEnso\Helpers\App\Classes\Obj;
use LaravelEnso\Tables\App\Exceptions\Template as Exception;
use LaravelEnso\Tables\App\Services\Template\Validators\Structure;
use Tests\TestCase;

class StructureTest extends TestCase
{
    private $validator;
    private $template;

    protected function setUp() :void
    {
        parent::setUp();

        // $this->withoutExceptionHandling();

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
    public function cannot_validate_with_wrong_attribute()
    {
        $this->template->set('wrong_attributes', 'wrong');

        $this->expectException(Exception::class);

        $this->expectExceptionMessage(Exception::unknownAttributes('wrong_attributes')->getMessage());

        $this->validate();
    }

    /** @test */
    public function cannot_validate_with_wrong_length_menu_format()
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
    public function cannot_validate_with_wrong_method()
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
    public function cannot_validate_with_wrong_comparison_operator()
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
