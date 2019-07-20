<?php

namespace Services\Template\Validators;

use Tests\TestCase;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\app\Exceptions\TemplateException;
use LaravelEnso\Tables\app\Services\Template\Validators\Structure;

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

        $this->expectException(TemplateException::class);

        $this->expectExceptionMessage('Mandatory Attribute(s) Missing: "routePrefix"');

        $this->validate();
    }

    /** @test */
    public function cannot_validate_with_wrong_attribute()
    {
        $this->template->set('wrong_attributes', 'wrong');

        $this->expectException(TemplateException::class);

        $this->expectExceptionMessage('Unknown Attribute(s) Found: "wrong_attributes"');

        $this->validate();
    }

    /** @test */
    public function cannot_validate_with_wrong_length_menu_format()
    {
        $this->template->set('lengthMenu', 'string');

        $this->expectException(TemplateException::class);

        $this->expectExceptionMessage('"lengthMenu" attribute must be an array');

        $this->validate();
    }

    /** @test */
    public function cannot_validate_with_non_numeric_debounce()
    {
        $this->template->set('debounce', 'string');

        $this->expectException(TemplateException::class);

        $this->expectExceptionMessage('"debounce" attribute must be an integer');

        $this->validate();
    }

    /** @test */
    public function cannot_validate_with_wrong_method()
    {
        $this->template->set('method', 'patch');

        $this->expectException(TemplateException::class);

        $this->expectExceptionMessage('"method" attribute can be either "GET" or "POST"');

        $this->validate();
    }

    /** @test */
    public function cannot_validate_with_non_boolean_selectable()
    {
        $this->template->set('selectable', 'string');

        $this->expectException(TemplateException::class);

        $this->expectExceptionMessage('"selectable" attribute must be a boolean');

        $this->validate();
    }

    /** @test */
    public function cannot_validate_with_wrong_comparison_operator()
    {
        $this->template->set('comparisonOperator', 'I_DONT_LIKE');

        $this->expectException(TemplateException::class);

        $this->expectExceptionMessage('"comparisonOperator" attribute can be either "LIKE" or "ILIKE"');

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
            'routePrefix' => 'prefix',
        ]);
    }
}
