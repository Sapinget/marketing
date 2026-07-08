<?php

namespace App\Support;

use InvalidArgumentException;
use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

class XlsxSheetReader
{
    /**
     * @return array<int, string>
     */
    public function sheetNames(string $path): array
    {
        if (! is_file($path)) {
            throw new InvalidArgumentException("XLSX file not found: {$path}");
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new RuntimeException("Unable to open XLSX file: {$path}");
        }

        try {
            $workbookXml = $zip->getFromName('xl/workbook.xml');
            if ($workbookXml === false) {
                throw new RuntimeException('Workbook metadata is incomplete.');
            }

            $workbook = new SimpleXMLElement($workbookXml);
            $workbook->registerXPathNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

            return array_map('strval', $workbook->xpath('//m:sheet/@name') ?: []);
        } finally {
            $zip->close();
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function rows(string $path, string $sheetName): array
    {
        if (! is_file($path)) {
            throw new InvalidArgumentException("XLSX file not found: {$path}");
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new RuntimeException("Unable to open XLSX file: {$path}");
        }

        try {
            $sheetPath = $this->sheetPath($zip, $sheetName);
            $sharedStrings = $this->sharedStrings($zip);
            $worksheetXml = $zip->getFromName($sheetPath);
            if ($worksheetXml === false) {
                throw new RuntimeException("Worksheet XML not found: {$sheetPath}");
            }

            $rows = $this->worksheetRows($worksheetXml, $sharedStrings);
            if ($rows === []) {
                return [];
            }

            $headers = array_map(fn ($value) => trim((string) $value), array_shift($rows));
            $headers = array_filter($headers, fn ($value) => $value !== '');
            $records = [];

            foreach ($rows as $row) {
                $record = [];
                $hasValue = false;

                foreach ($headers as $index => $header) {
                    $value = $row[$index] ?? null;
                    if ($header === 'Tanggal_Rencana') {
                        $value = $this->normalizeDate($value);
                    }
                    if ($value !== null && $value !== '') {
                        $hasValue = true;
                    }
                    $record[$header] = $value;
                }

                if ($hasValue) {
                    $records[] = $record;
                }
            }

            return $records;
        } finally {
            $zip->close();
        }
    }

    private function sheetPath(ZipArchive $zip, string $sheetName): string
    {
        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');

        if ($workbookXml === false || $relsXml === false) {
            throw new RuntimeException('Workbook metadata is incomplete.');
        }

        $workbook = new SimpleXMLElement($workbookXml);
        $workbook->registerXPathNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $workbook->registerXPathNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');

        $sheetRelId = null;
        foreach ($workbook->xpath('//m:sheet') ?: [] as $sheet) {
            if ((string) $sheet['name'] === $sheetName) {
                $attributes = $sheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships');
                $sheetRelId = (string) $attributes['id'];
                break;
            }
        }

        if (! $sheetRelId) {
            throw new InvalidArgumentException("Sheet not found: {$sheetName}");
        }

        $rels = new SimpleXMLElement($relsXml);
        foreach ($rels->Relationship as $relationship) {
            if ((string) $relationship['Id'] === $sheetRelId) {
                $target = (string) $relationship['Target'];
                return 'xl/'.ltrim($target, '/');
            }
        }

        throw new RuntimeException("Relationship not found for sheet: {$sheetName}");
    }

    /**
     * @return array<int, string>
     */
    private function sharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');
        if ($xml === false) {
            return [];
        }

        $strings = [];
        $shared = new SimpleXMLElement($xml);
        $shared->registerXPathNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

        foreach ($shared->xpath('//m:si') ?: [] as $item) {
            $item->registerXPathNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            $parts = [];
            foreach ($item->xpath('.//m:t') ?: [] as $text) {
                $parts[] = (string) $text;
            }
            $strings[] = implode('', $parts);
        }

        return $strings;
    }

    /**
     * @param  array<int, string>  $sharedStrings
     * @return array<int, array<int, mixed>>
     */
    private function worksheetRows(string $xml, array $sharedStrings): array
    {
        $worksheet = new SimpleXMLElement($xml);
        $worksheet->registerXPathNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $rows = [];

        foreach ($worksheet->xpath('//m:sheetData/m:row') ?: [] as $row) {
            $row->registerXPathNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            $values = [];
            foreach ($row->xpath('m:c') ?: [] as $cell) {
                $cell->registerXPathNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
                $ref = (string) $cell['r'];
                $columnIndex = $this->columnIndex($ref);
                $values[$columnIndex] = $this->cellValue($cell, $sharedStrings);
            }
            if ($values !== []) {
                ksort($values);
                $rows[] = $this->denseRow($values);
            }
        }

        return $rows;
    }

    /**
     * @param  array<int, mixed>  $values
     * @return array<int, mixed>
     */
    private function denseRow(array $values): array
    {
        $max = max(array_keys($values));
        $row = [];
        for ($index = 0; $index <= $max; $index++) {
            $row[] = $values[$index] ?? null;
        }

        return $row;
    }

    /**
     * @param  array<int, string>  $sharedStrings
     */
    private function cellValue(SimpleXMLElement $cell, array $sharedStrings): mixed
    {
        $type = (string) $cell['t'];

        if ($type === 'inlineStr') {
            $cell->registerXPathNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            return implode('', array_map('strval', $cell->xpath('.//m:t') ?: []));
        }

        $raw = isset($cell->v) ? (string) $cell->v : null;
        if ($raw === null || $raw === '') {
            return null;
        }

        if ($type === 's') {
            return $sharedStrings[(int) $raw] ?? '';
        }

        if ($type === 'b') {
            return $raw === '1';
        }

        return is_numeric($raw) ? (float) $raw : $raw;
    }

    private function columnIndex(string $cellRef): int
    {
        preg_match('/^[A-Z]+/', $cellRef, $matches);
        $letters = $matches[0] ?? 'A';
        $index = 0;

        foreach (str_split($letters) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }

        return $index - 1;
    }

    private function normalizeDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $base = new \DateTimeImmutable('1899-12-30');
            return $base->modify('+'.(int) $value.' days')->format('Y-m-d');
        }

        $timestamp = strtotime((string) $value);

        return $timestamp ? date('Y-m-d', $timestamp) : (string) $value;
    }
}
