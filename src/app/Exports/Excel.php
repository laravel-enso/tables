<?php

namespace LaravelEnso\VueDatatable\app\Exports;

use Box\Spout\Common\Type;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Writer\Style\StyleBuilder;

class Excel
{
    private $fullPath;
    private $header;
    private $data;
    private $writer;

    public function __construct(string $fullPath, $header, $data)
    {
        $this->fullPath = $fullPath;
        $this->header = $header;
        $this->data = $data;
    }

    public function run()
    {
        $this->setWriter();

        ini_set('max_execution_time', config('enso.datatable.export.maxExecutionTime'));

        $this->writer
            ->getCurrentSheet()
            ->setName('Sheet1');

        $this->writer->addRow($this->header)
            ->addRows($this->data)
            ->close();

        unset($this->writer);
    }

    private function setWriter()
    {
        $defaultStyle = (new StyleBuilder())
            ->setShouldWrapText(false)
            ->build();

        $this->writer = WriterFactory::create(Type::XLSX);

        $this->writer->setDefaultRowStyle($defaultStyle)
            ->openToFile($this->fullPath);
    }
}
