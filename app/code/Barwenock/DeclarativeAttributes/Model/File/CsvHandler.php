<?php
/**
 * @author Barwenock
 * @copyright Copyright (c) Barwenock
 * @package Declarative Attributes Import for Magento 2
 */

declare(strict_types=1);

namespace Barwenock\DeclarativeAttributes\Model\File;

class CsvHandler
{
    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csv;

    /**
     * @param \Magento\Framework\File\Csv $csv
     */
    public function __construct(
        \Magento\Framework\File\Csv $csv
    ) {
        $this->csv = $csv;
    }

    /**
     * Reads a CSV file from the specified path and processes its content
     *
     * @param string $filePath
     * @return array
     */
    public function readCsvFile(string $filePath): array
    {
        $data = $this->csv->getData($filePath);

        $headers = array_shift($data);

        $attributes = [];
        foreach ($data as $row) {
            $attributes[] = array_combine($headers, $row);
        }

        return $this->processAttributes($attributes);
    }

    /**
     * Processes the provided attributes by converting options to an array
     *
     * @param array $attributes
     * @return array
     */
    protected function processAttributes($attributes)
    {
        foreach ($attributes as $index => $attribute) {
            $attributes[$index]['options'] = isset($attribute['options'])
                ? explode('|', $attribute['options']) : [];
        }

        return $attributes;
    }
}
