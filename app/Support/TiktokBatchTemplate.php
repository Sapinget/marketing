<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMXPath;
use InvalidArgumentException;
use RuntimeException;
use ZipArchive;

/**
 * Reads and rewrites the TikTok Seller Center "batch edit" workbook.
 *
 * The workbook is patched in place (sheet XML + shared strings only) so hidden
 * sheets, dropdown validations, conditional formats and sheet protection stay
 * byte-for-byte what TikTok generated.
 *
 * The Shopee "mass update" workbook (single sheet, header keys prefixed
 * `et_title_`) goes through the same code path with a different layout.
 */
class TiktokBatchTemplate
{
    private const NS = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

    private const REL_NS = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';

    private const PKG_REL_NS = 'http://schemas.openxmlformats.org/package/2006/relationships';

    private const TEMPLATE_SHEET = 'Template';

    private const BRAND_SHEET = 'Brand';

    private const FIRST_DATA_ROW = 6;

    private const SHOPEE_FIRST_DATA_ROW = 7;

    private const SHOPEE_READONLY_KEYS = [
        'et_title_product_id', 'et_title_product_name', 'et_title_variation_id', 'et_title_variation_name', 'et_title_reason',
    ];

    /** Column keys the stock lookup and the editor need, per platform. */
    private const FIELDS = [
        'tiktok' => ['name' => 'product_name', 'variation' => 'variation_value', 'stock' => 'quantity', 'price' => 'price'],
        'shopee' => ['name' => 'et_title_product_name', 'variation' => 'et_title_variation_name', 'stock' => 'et_title_variation_stock', 'price' => 'et_title_variation_price'],
    ];

    private const MAX_ROWS = 5000;

    public const STATUS_OPTIONS = ['Aktif(1)', 'Dinonaktifkan(2)'];

    /**
     * @return array{platform: string, fields: array<string, string>, columns: array<int, array<string, mixed>>, rows: array<int, array<int, string>>, brands: array<string, array<int, string>>, statusOptions: array<int, string>, maxRows: int}
     */
    public function parse(string $path): array
    {
        $zip = $this->open($path);

        try {
            $sheetPath = $this->templateSheetPath($zip);
            $strings = $this->sharedStrings($zip);
            $grid = $this->readGrid($this->loadXml($zip, $sheetPath), $strings);

            $keys = $grid[1] ?? [];
            $platform = $this->platform($keys);
            $firstDataRow = $this->firstDataRow($platform);
            $columnCount = 0;
            foreach ($keys as $index => $value) {
                if ($value !== '') {
                    $columnCount = $index + 1;
                }
            }
            if ($columnCount === 0 || $platform === null) {
                throw new InvalidArgumentException('File bukan template batch edit TikTok / update massal Shopee (header product_id tidak ditemukan).');
            }

            $columns = [];
            for ($i = 0; $i < $columnCount; $i++) {
                $key = (string) ($keys[$i] ?? '');
                $hint = (string) ($grid[5][$i] ?? '');
                $columns[] = [
                    'index' => $i,
                    'key' => $key,
                    'label' => (string) ($grid[3][$i] ?? $keys[$i] ?? ''),
                    'requirement' => (string) ($grid[4][$i] ?? ''),
                    'hint' => $hint,
                    'readonly' => $platform === 'shopee'
                        ? in_array($key, self::SHOPEE_READONLY_KEYS, true)
                        : str_starts_with($hint, 'Tidak dapat diedit'),
                ];
            }

            $rows = [];
            foreach ($grid as $rowNumber => $cells) {
                if ($rowNumber < $firstDataRow) {
                    continue;
                }
                $row = [];
                for ($i = 0; $i < $columnCount; $i++) {
                    $row[] = (string) ($cells[$i] ?? '');
                }
                if (trim($row[0]) === '' && trim($row[1] ?? '') === '') {
                    continue;
                }
                $rows[] = $row;
            }

            return [
                'platform' => $platform,
                'fields' => self::FIELDS[$platform],
                'columns' => $columns,
                'rows' => $rows,
                'brands' => $this->brands($zip, $strings),
                'statusOptions' => self::STATUS_OPTIONS,
                'maxRows' => $this->lastValidatedRow($zip, $sheetPath, $firstDataRow) - $firstDataRow + 1,
            ];
        } finally {
            $zip->close();
        }
    }

    /**
     * Writes $rows into a copy of the uploaded workbook at $outputPath.
     *
     * @param  array<int, array<int, mixed>>  $rows
     */
    public function build(string $sourcePath, array $rows, string $outputPath): void
    {
        if (count($rows) > self::MAX_ROWS) {
            throw new InvalidArgumentException('Baris terlalu banyak.');
        }

        if (! @copy($sourcePath, $outputPath)) {
            throw new RuntimeException('Gagal menyiapkan file output.');
        }

        $zip = $this->open($outputPath);

        try {
            $sheetPath = $this->templateSheetPath($zip);
            $sheetXml = $this->loadXml($zip, $sheetPath);
            $firstDataRow = $this->firstDataRow($this->platform($this->readGrid($sheetXml, $this->sharedStrings($zip))[1] ?? []) ?? 'tiktok');
            $lastRow = $this->lastValidatedRow($zip, $sheetPath, $firstDataRow);
            if ($firstDataRow + count($rows) - 1 > $lastRow) {
                throw new InvalidArgumentException(sprintf(
                    'Template hanya menyediakan %d baris produk, data berisi %d baris.',
                    $lastRow - $firstDataRow + 1,
                    count($rows)
                ));
            }

            $sstDoc = $this->loadXml($zip, 'xl/sharedStrings.xml');
            $sheetDoc = $this->loadXml($zip, $sheetPath);

            $sst = new SharedStringTable($sstDoc);
            $columnCount = $this->columnCount($sheetDoc, $sst);

            $xpath = new DOMXPath($sheetDoc);
            $xpath->registerNamespace('m', self::NS);

            /** @var DOMElement $sheetData */
            $sheetData = $xpath->query('//m:sheetData')->item(0);
            $existing = [];
            foreach ($xpath->query('//m:sheetData/m:row') as $rowEl) {
                $existing[(int) $rowEl->getAttribute('r')] = $rowEl;
            }

            $lastExisting = $existing === [] ? 0 : max(array_keys($existing));
            $writeTo = max($lastExisting, $firstDataRow + count($rows) - 1);

            for ($rowNumber = $firstDataRow; $rowNumber <= $writeTo; $rowNumber++) {
                $index = $rowNumber - $firstDataRow;
                $values = $rows[$index] ?? null;
                $rowEl = $existing[$rowNumber] ?? null;

                if ($rowEl === null) {
                    if ($values === null) {
                        continue;
                    }
                    $rowEl = $sheetDoc->createElementNS(self::NS, 'row');
                    $rowEl->setAttribute('r', (string) $rowNumber);
                    $this->insertRow($sheetData, $rowEl);
                }

                for ($col = 0; $col < $columnCount; $col++) {
                    $value = $values === null ? '' : $this->cellText($values[$col] ?? '');
                    $this->setCell($sheetDoc, $rowEl, $col, $rowNumber, $value, $sst);
                }
            }

            // The export is meant to be edited further in Excel, so drop the template's sheet lock.
            foreach (iterator_to_array($sheetDoc->getElementsByTagNameNS(self::NS, 'sheetProtection')) as $protection) {
                $protection->parentNode?->removeChild($protection);
            }

            $zip->addFromString($sheetPath, $sheetDoc->saveXML());
            $zip->addFromString('xl/sharedStrings.xml', $sst->save());
        } finally {
            $zip->close();
        }
    }

    /**
     * @param  array<int, string>  $keys  header row (row 1) of the data sheet
     */
    private function platform(array $keys): ?string
    {
        return match ($keys[0] ?? '') {
            'product_id' => 'tiktok',
            'et_title_product_id' => 'shopee',
            default => null,
        };
    }

    private function firstDataRow(string $platform): int
    {
        return $platform === 'shopee' ? self::SHOPEE_FIRST_DATA_ROW : self::FIRST_DATA_ROW;
    }

    /** TikTok keeps its data on "Template"; the Shopee export is a single sheet. */
    private function templateSheetPath(ZipArchive $zip): string
    {
        try {
            return $this->sheetPath($zip, self::TEMPLATE_SHEET);
        } catch (RuntimeException) {
            $workbook = $this->loadXml($zip, 'xl/workbook.xml');
            $first = $workbook->getElementsByTagNameNS(self::NS, 'sheet')->item(0);
            if (! $first instanceof DOMElement) {
                throw new RuntimeException('File XLSX tidak punya sheet.');
            }

            return $this->sheetPath($zip, $first->getAttribute('name'));
        }
    }

    private function cellText(mixed $value): string
    {
        if ($value === null || is_array($value) || is_object($value)) {
            return '';
        }
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        if (is_float($value) && floor($value) === $value && abs($value) < 1e15) {
            return (string) (int) $value;
        }

        return (string) $value;
    }

    private function columnCount(DOMDocument $sheetDoc, SharedStringTable $sst): int
    {
        $xpath = new DOMXPath($sheetDoc);
        $xpath->registerNamespace('m', self::NS);
        $count = 0;
        foreach ($xpath->query('//m:sheetData/m:row[@r="1"]/m:c') as $cell) {
            $text = $this->cellValue($cell, $sst->all());
            if ($text !== '') {
                $count = max($count, $this->columnIndex($cell->getAttribute('r')) + 1);
            }
        }

        return $count;
    }

    private function setCell(DOMDocument $doc, DOMElement $rowEl, int $col, int $rowNumber, string $value, SharedStringTable $sst): void
    {
        $ref = $this->columnName($col).$rowNumber;
        $cell = null;
        $before = null;
        foreach ($rowEl->childNodes as $child) {
            if (! $child instanceof DOMElement || $child->localName !== 'c') {
                continue;
            }
            $childCol = $this->columnIndex($child->getAttribute('r'));
            if ($childCol === $col) {
                $cell = $child;
                break;
            }
            if ($childCol > $col) {
                $before = $child;
                break;
            }
        }

        if ($cell === null) {
            if ($value === '') {
                return;
            }
            $cell = $doc->createElementNS(self::NS, 'c');
            $cell->setAttribute('r', $ref);
            $rowEl->insertBefore($cell, $before);
        }

        while ($cell->firstChild) {
            $cell->removeChild($cell->firstChild);
        }
        $cell->removeAttribute('t');

        if ($value === '') {
            return;
        }

        $cell->setAttribute('t', 's');
        $cell->appendChild($doc->createElementNS(self::NS, 'v', (string) $sst->indexOf($value)));
    }

    private function insertRow(DOMElement $sheetData, DOMElement $newRow): void
    {
        $number = (int) $newRow->getAttribute('r');
        foreach ($sheetData->childNodes as $child) {
            if ($child instanceof DOMElement && (int) $child->getAttribute('r') > $number) {
                $sheetData->insertBefore($newRow, $child);

                return;
            }
        }
        $sheetData->appendChild($newRow);
    }

    private function lastValidatedRow(ZipArchive $zip, string $sheetPath, int $firstDataRow): int
    {
        $xml = $zip->getFromName($sheetPath);
        if ($xml !== false && preg_match_all('/<dataValidation\b[^>]*\bsqref="C(\d+):C(\d+)"/', $xml, $matches) && $matches[2] !== []) {
            return max(array_map('intval', $matches[2]));
        }

        // Sheets without row validations (Shopee) have no row cap of their own.
        return $firstDataRow + self::MAX_ROWS - 1;
    }

    /**
     * @param  array<int, string>  $strings
     * @return array<string, array<int, string>>
     */
    private function brands(ZipArchive $zip, array $strings): array
    {
        try {
            $path = $this->sheetPath($zip, self::BRAND_SHEET);
        } catch (RuntimeException) {
            return [];
        }

        $brands = [];
        foreach ($this->readGrid($this->loadXml($zip, $path), $strings) as $rowNumber => $cells) {
            $category = trim((string) ($cells[0] ?? ''));
            $brand = trim((string) ($cells[1] ?? ''));
            if ($rowNumber < 2 || $category === '' || $brand === '') {
                continue;
            }
            $brands[$category][] = $brand;
        }

        return $brands;
    }

    /**
     * @param  array<int, string>  $strings
     * @return array<int, array<int, string>> row number => column index => text
     */
    private function readGrid(DOMDocument $doc, array $strings): array
    {
        $xpath = new DOMXPath($doc);
        $xpath->registerNamespace('m', self::NS);

        $grid = [];
        foreach ($xpath->query('//m:sheetData/m:row/m:c') as $cell) {
            $text = $this->cellValue($cell, $strings);
            if ($text === '') {
                continue;
            }
            $ref = $cell->getAttribute('r');
            $grid[(int) preg_replace('/^[A-Z]+/', '', $ref)][$this->columnIndex($ref)] = $text;
        }

        return $grid;
    }

    /**
     * @param  array<int, string>  $strings
     */
    private function cellValue(DOMElement $cell, array $strings): string
    {
        $type = $cell->getAttribute('t');

        if ($type === 'inlineStr') {
            return $cell->textContent;
        }

        $valueNode = null;
        foreach ($cell->childNodes as $child) {
            if ($child instanceof DOMElement && $child->localName === 'v') {
                $valueNode = $child;
                break;
            }
        }
        if ($valueNode === null) {
            return '';
        }

        return $type === 's' ? ($strings[(int) $valueNode->textContent] ?? '') : $valueNode->textContent;
    }

    /**
     * @return array<int, string>
     */
    private function sharedStrings(ZipArchive $zip): array
    {
        $strings = [];
        $xml = $zip->getFromName('xl/sharedStrings.xml');
        if ($xml === false) {
            return $strings;
        }

        $doc = new DOMDocument;
        $doc->loadXML($xml, LIBXML_NONET | LIBXML_COMPACT | LIBXML_PARSEHUGE);
        foreach ($doc->documentElement->childNodes as $si) {
            if (! $si instanceof DOMElement || $si->localName !== 'si') {
                continue;
            }
            $text = '';
            foreach ($si->getElementsByTagNameNS(self::NS, 't') as $t) {
                if ($t->parentNode instanceof DOMElement && $t->parentNode->localName === 'rPh') {
                    continue;
                }
                $text .= $t->textContent;
            }
            $strings[] = $text;
        }

        return $strings;
    }

    private function sheetPath(ZipArchive $zip, string $sheetName): string
    {
        $workbook = $this->loadXml($zip, 'xl/workbook.xml');
        $rels = $this->loadXml($zip, 'xl/_rels/workbook.xml.rels');

        $relId = null;
        foreach ($workbook->getElementsByTagNameNS(self::NS, 'sheet') as $sheet) {
            if ($sheet->getAttribute('name') === $sheetName) {
                $relId = $sheet->getAttributeNS(self::REL_NS, 'id');
                break;
            }
        }
        if ($relId === null) {
            throw new RuntimeException("Sheet \"{$sheetName}\" tidak ditemukan di file.");
        }

        foreach ($rels->getElementsByTagNameNS(self::PKG_REL_NS, 'Relationship') as $rel) {
            if ($rel->getAttribute('Id') === $relId) {
                $target = ltrim($rel->getAttribute('Target'), '/');

                return str_starts_with($target, 'xl/') ? $target : 'xl/'.$target;
            }
        }

        throw new RuntimeException("Relasi sheet \"{$sheetName}\" tidak ditemukan.");
    }

    private function loadXml(ZipArchive $zip, string $name): DOMDocument
    {
        $xml = $zip->getFromName($name);
        if ($xml === false) {
            throw new RuntimeException("Bagian {$name} tidak ada di file XLSX.");
        }

        $doc = new DOMDocument;
        $doc->preserveWhiteSpace = true;
        if (! $doc->loadXML($xml, LIBXML_NONET | LIBXML_PARSEHUGE)) {
            throw new RuntimeException("Bagian {$name} rusak.");
        }

        return $doc;
    }

    private function open(string $path): ZipArchive
    {
        if (! is_file($path)) {
            throw new InvalidArgumentException('File XLSX tidak ditemukan.');
        }

        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            throw new InvalidArgumentException('File bukan XLSX yang valid.');
        }

        return $zip;
    }

    private function columnIndex(string $ref): int
    {
        preg_match('/^([A-Z]+)/', $ref, $m);
        $index = 0;
        foreach (str_split($m[1] ?? 'A') as $char) {
            $index = $index * 26 + (ord($char) - 64);
        }

        return $index - 1;
    }

    private function columnName(int $index): string
    {
        $name = '';
        for ($n = $index + 1; $n > 0; $n = intdiv($n - 1, 26)) {
            $name = chr(65 + (($n - 1) % 26)).$name;
        }

        return $name;
    }
}
