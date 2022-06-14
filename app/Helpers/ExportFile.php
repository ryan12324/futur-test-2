<?php

namespace App\Helpers;

use App\Helpers\ExportFile\ExportFileRow;
use League\Csv\Writer;

class ExportFile
{
    /**
     * @var array An array containing the export files input data
     */
    private array $inputData;

    /**
     * @var array An array of ExportFileRows
     */
    private array $outputData = [];

    /**
     * @param array $inputData An array containing the input data to be formatted
     */
    public function __construct(array $inputData)
    {
        $this->inputData = $inputData;
        $this->formatInputData();
    }

    /**
     * Format the input data using the ExportFileRow class so that it is in the correct export format
     * @return void
     */
    private function formatInputData() {
        foreach($this->inputData as $key => $datum) {
            $row = new ExportFileRow($key, $datum);
            $this->outputData[] = $row->toArray();
        }
    }

    /**
     * Return a string containing a csv representation of the ExportFile data
     * @return string
     * @throws \League\Csv\CannotInsertRecord
     * @throws \League\Csv\Exception
     */
    public function exportAsCsv() {
        try {
            $header = array_keys($this->outputData[0]);
            $csv = Writer::createFromString();
            $csv->insertOne($header);
            $csv->insertAll($this->outputData);
            return $csv->toString();
        } catch(\League\Csv\CannotInsertRecord $e) {
            throw new \Exception("Error inserting record into csv string",0, $e);
        }
    }

}