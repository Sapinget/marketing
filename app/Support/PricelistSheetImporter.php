<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PricelistSheetImporter
{
    public const SPREADSHEET_ID = '1SngAcw96zILCFqaSgpi6mgRXRnmZdrJzfFeGtVS25iw';

    public const BRAND_SHEETS = [
        'SAMSUNG',
        'XIAOMI',
        'OPPO',
        'VIVO',
        'HUAWEI',
        'TECNO',
        'INFINIX',
        'NUBIA',
        'REALME',
        'ITEL',
        'HONOR',
    ];

    /**
     * @var null|callable(string): string
     */
    private $csvFetcher = null;

    public function setCsvFetcher(?callable $csvFetcher): self
    {
        $this->csvFetcher = $csvFetcher;

        return $this;
    }

    /**
     * @param  array<int, string>|null  $sheets
     * @return array{status: string, sheets: array<int, array<string, mixed>>, imported: int, skipped: int, failed: int}
     */
    public function import(?array $sheets = null): array
    {
        $targetSheets = array_values(array_intersect($sheets ?: self::BRAND_SHEETS, self::BRAND_SHEETS));
        $summary = [
            'status' => 'success',
            'sheets' => [],
            'imported' => 0,
            'skipped' => 0,
            'failed' => 0,
        ];

        foreach ($targetSheets as $sheetName) {
            try {
                $rows = $this->parseCsv($this->fetchCsv($sheetName));
                $sheetSummary = $this->importRows($sheetName, $rows);
                $summary['sheets'][] = $sheetSummary;
                $summary['imported'] += $sheetSummary['imported'];
                $summary['skipped'] += $sheetSummary['skipped'];
            } catch (\Throwable $exception) {
                $summary['failed']++;
                $summary['sheets'][] = [
                    'sheet' => $sheetName,
                    'imported' => 0,
                    'skipped' => 0,
                    'error' => $exception->getMessage(),
                ];
            }
        }

        if ($summary['failed'] > 0 && $summary['imported'] === 0) {
            $summary['status'] = 'failed';
        } elseif ($summary['failed'] > 0) {
            $summary['status'] = 'partial';
        }

        return $summary;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function parseCsv(string $csv): array
    {
        $lines = preg_split('/\r\n|\n|\r/', trim($csv));
        if (! is_array($lines) || $lines === []) {
            return [];
        }

        $rawHeaders = str_getcsv((string) array_shift($lines), ',', '"', '\\');
        $headers = array_map(fn ($header) => $this->normalizeHeader((string) $header), $rawHeaders);
        $rows = [];

        foreach ($lines as $lineNumber => $line) {
            if (trim((string) $line) === '') {
                continue;
            }

            $values = str_getcsv((string) $line, ',', '"', '\\');
            $row = [];
            $hasValue = false;

            foreach ($headers as $index => $header) {
                if ($header === '') {
                    continue;
                }

                $value = $values[$index] ?? null;
                if ($value !== null) {
                    $value = trim((string) $value);
                }
                if ($value !== null && $value !== '') {
                    $hasValue = true;
                }
                $row[$header] = $value;
            }

            if ($hasValue) {
                $row['_source_row'] = $lineNumber + 2;
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array{sheet: string, imported: int, skipped: int}
     */
    public function importRows(string $sheetName, array $rows): array
    {
        $now = now();
        $imported = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $urut = $this->integerValue($row['URUT'] ?? null);
            if ($urut === null) {
                $skipped++;

                continue;
            }

            $sourceRow = (int) ($row['_source_row'] ?? 0);
            $kategori = $this->categoryValue($row, $sheetName);
            $namaProduk = $this->firstString($row, ['NAMA PRODUK', 'NAMA', 'PRODUK', 'TYPE', 'TIPE', 'SERI']);
            $brand = $this->firstString($row, ['BRAND']) ?: $sheetName;
            $storage = $this->firstString($row, ['STORAGE', 'MEMORY', 'INTERNAL']);
            $ram = $this->firstString($row, ['RAM']);
            $warna = $this->firstString($row, ['WARNA', 'COLOR']);
            $hargaNasional = $this->moneyValue($row['HARGA NASIONAL'] ?? null);
            $specialPrice = $this->moneyValue($row['SPECIAL PRICE'] ?? ($row['SPESIAL PRICE'] ?? null));
            $hargaSpesial = $this->moneyValue($row['HARGA SPESIAL'] ?? ($row['SPESIAL PRICE'] ?? null));
            $hargaSrp = $this->moneyValue($row['HARGA SRP'] ?? null);
            $hargaJual = $this->moneyValue($row['HARGA JUAL'] ?? null);
            $hargaOnline = $this->moneyValue($row['HARGA ONLINE'] ?? null);
            $hargaModal = $this->moneyValue($row['HARGA MODAL'] ?? null);
            $normalizedKey = $this->normalizedKey($sheetName, $namaProduk, $storage, $ram, $warna, $sourceRow);

            DB::table('pricelist_products')->updateOrInsert(
                ['source_sheet' => $sheetName, 'source_row' => $sourceRow],
                [
                    'source_id' => 'PL'.str_pad((string) abs(crc32($sheetName.'|'.$sourceRow)), 10, '0', STR_PAD_LEFT),
                    'urut' => $urut,
                    'kategori' => $kategori,
                    'brand' => $brand,
                    'nama_produk' => $namaProduk,
                    'storage' => $storage,
                    'ram' => $ram,
                    'warna' => $warna,
                    'harga_nasional' => $hargaNasional,
                    'special_price' => $specialPrice,
                    'harga_spesial' => $hargaSpesial,
                    'harga_srp' => $hargaSrp,
                    'harga_jual' => $hargaJual,
                    'harga_online' => $hargaOnline,
                    'harga_modal' => $hargaModal,
                    'harga_lainnya' => json_encode($this->otherPrices($row), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'normalized_key' => $normalizedKey,
                    'is_active' => true,
                    'raw_payload' => json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'imported_at' => $now,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );

            $imported++;
        }

        return [
            'sheet' => $sheetName,
            'imported' => $imported,
            'skipped' => $skipped,
        ];
    }

    private function fetchCsv(string $sheetName): string
    {
        if ($this->csvFetcher !== null) {
            return (string) call_user_func($this->csvFetcher, $sheetName);
        }

        $url = sprintf(
            'https://docs.google.com/spreadsheets/d/%s/gviz/tq?tqx=out:csv&sheet=%s',
            self::SPREADSHEET_ID,
            rawurlencode($sheetName)
        );

        $context = stream_context_create([
            'http' => [
                'timeout' => 60,
                'ignore_errors' => true,
                'user_agent' => 'PuraPuraPonselDashboard/1.0',
            ],
        ]);

        $csv = @file_get_contents($url, false, $context);

        if ($csv === false) {
            throw new \RuntimeException("Gagal mengambil data sheet {$sheetName} dari Google Spreadsheet.");
        }

        return $csv;
    }

    private function normalizeHeader(string $header): string
    {
        $header = str_replace(["\xc2\xa0", "\n", "\r"], ' ', $header);
        $header = preg_replace('/\s+/', ' ', trim($header)) ?: '';

        return Str::upper($header);
    }

    private function firstString(array $row, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = trim((string) ($row[$key] ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function categoryValue(array $row, string $sheetName): ?string
    {
        foreach (['KATEGORI', $sheetName.' KATEGORI'] as $key) {
            $value = trim((string) ($row[$key] ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        foreach ($row as $key => $value) {
            if (str_ends_with((string) $key, ' KATEGORI') && trim((string) $value) !== '') {
                return trim((string) $value);
            }
        }

        return null;
    }

    private function integerValue(mixed $value): ?int
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $value = preg_replace('/[^0-9-]/', '', $value) ?: '';

        return $value === '' ? null : (int) $value;
    }

    private function moneyValue(mixed $value): ?int
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $value = preg_replace('/[^0-9-]/', '', $value) ?: '';

        return $value === '' ? null : (int) $value;
    }

    private function normalizedKey(string $sheetName, ?string $name, ?string $storage, ?string $ram, ?string $warna, int $sourceRow): string
    {
        $parts = array_filter([$sheetName, $name, $storage, $ram, $warna], fn ($part) => filled($part));
        $key = implode('|', array_map(fn ($part) => Str::upper(preg_replace('/\s+/', ' ', trim((string) $part)) ?: ''), $parts));

        return $key !== '' ? $key : $sheetName.'|ROW|'.$sourceRow;
    }

    /**
     * @return array<string, int|null>
     */
    private function otherPrices(array $row): array
    {
        $priceHeaders = [
            'HARGA PG',
            'HARGA 10%',
            'HARGA',
            'KREDIT',
            'CASHBACK',
            'SELISIH SRP',
        ];
        $prices = [];

        foreach ($priceHeaders as $header) {
            if (array_key_exists($header, $row)) {
                $prices[$header] = $this->moneyValue($row[$header]);
            }
        }

        return $prices;
    }
}
