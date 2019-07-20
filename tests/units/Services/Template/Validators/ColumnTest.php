<?php

namespace Services\Template\Validators;

use Tests\TestCase;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\app\Exceptions\TemplateException;
use LaravelEnso\Tables\app\Attributes\Column as Attributes;
use LaravelEnso\Tables\app\Services\Template\Validators\Columns;

class ColumnTest extends TestCase
{

    /** @test */
    public function cannot_validate_without_mandatory_attribute()
    {
        $this->validate(
            [
                'columns' => [
                    'label' => new Obj(['r']),
                    'name' => ['r'],
                ]
            ],
            false
        );
    }

    /** @test */
    public function cannot_validate_with_wrong_attribute()
    {
        $this->validate(
            $this->basicTemplate([
                'wrong_attribute' => 'r',
            ]),
            false
        );
    }

    /** @test */
    public function cannot_validate_with_wrong_enum()
    {
        $this->validate(
            $this->basicTemplate([
                'enum' => 'af',
            ]),
            false
        );
    }

    /** @test */
    public function cannot_validate_with_wrong_format()
    {
        $this->validate(
            $this->basicTemplate([
                'lengthMenu' => 'NOT_ARRAY',
            ]),
            false
        );

        $this->validate(
            $this->basicTemplate([
                'debounce' => 'NOT_NUMBER',
            ]),
            false
        );

        $this->validate(
            $this->basicTemplate([
                'method' => 'NOT_METHOD',
            ]),
            false
        );

        $this->validate(
            $this->basicTemplate([
                'selectable' => 'NOT_BOOL',
            ]),
            false
        );

        $this->validate(
            $this->basicTemplate([
                'comparisonOperator' => 'NOT_LIKE',
            ]),
            false
        );
    }

    /** @test */
    public function can_validate()
    {
        $this->validate(
            $this->basicTemplate([
                'tooltip' => 'test'
            ]),
            true
        );

    }

    private function basicTemplate($columns = [])
    {
        if (!isset($columns[0]))
            $columns = [$columns];

        $mandatoryAttributes = collect(Attributes::Mandatory)->flip()->map(function(){
            return new Obj([]);
        });

        $columns = collect($columns)->map(function($col) use($mandatoryAttributes) {
            return new Obj($mandatoryAttributes->merge($col));
        });

        return [
            "columns" => $columns
        ];
    }

    private function validate(array $template, bool $isValid)
    {
        $validator = new Columns(
            new Obj($template)
        );

        try {
            $validator->validate();

            if (!$isValid)
                $this->fail('should throw TemplateException');
        } catch (TemplateException $e) {
            if ($isValid)
                $this->fail('should not throw TemplateException' . PHP_EOL . $e);
        }

        $this->assertTrue(true);
    }

}
