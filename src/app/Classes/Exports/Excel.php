<?php

namespace LaravelEnso\VueDatatable\app\Exports;

use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\WriterFactory;

class Excel
{
    private $fullPath;
    private $data;
    private $writer;

    public function __construct(string $fullPath, array $data)
    {
        $this->fullPath = $fullPath;
        $this->data = $data;
    }

    public function run()
    {
        $this->setWriter($fullPath);

        ini_set('max_execution_time', config('enso.datatable.export.maxExecutionTime'));

        $this->writer->getCurrentSheet();

        $this->addRows();

        $this->writer->close();

        unset($this->writer);
    }

    private function setWriter()
    {
        $defaultStyle = (new StyleBuilder())
            ->setShouldWrapText(false)
            ->build();

        $this->writer = WriterFactory::create(Type::XLSX);

        $this->writer->setDefaultRowStyle($defaultStyle)
            ->openToFile($this->fullFilePath);
    }

    private function addRows()
    {
        $this->writer->addRow($this->getHeader()->toArray());

        $this->writer->addRows($this->data->toArray());
    }

    private function getHeader()
    {
        return collect($this->data->first())->keys()
            ->map(function ($header, $column) {
                return __($column);
            });
    }
}
