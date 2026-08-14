<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class AppleSheetImporter
{
    public const SPREADSHEET_ID = '1yyGVNENagRd1LGbo0szwy8io9XPgEaCr6Mr8hWroKs8';

    public const SHEETS = [
        'IPHONE',
        'IPAD',
        'MACBOOK',
        'APPLE WATCH',
        'AIRPODS',
        'APPLE PENCIL',
    ];

    /**
     * Per-sheet column-index configuration.
     *
     * seri_col        : column index for model name (carry-forwarded when empty)
     * storage_col     : column index for storage/size variant (null = no variant)
     * ram_col         : column index for RAM (MacBook); prepended to storage with "/"
     * harga_nasional  : column index for SRP / reference price (strikethrough in catalog)
     * special_price   : column index for main toko/cash price (no fallback if "-")
     * carry_seri      : true when only the first row of a model group has the name
     * kondisi_cols    : map of label => column index for all condition price columns
     */
    private const SHEET_CONFIG = [
        'IPHONE' => [
            'seri_col' => 0,
            'storage_col' => 1,
            'ram_col' => null,
            'harga_nasional' => 13, // SRP NEW (harga coret)
            'special_price' => 14, // HARGA TOKO NEW (no fallback)
            'carry_seri' => true,
            'kondisi_cols' => [
                'TIDAK TERDAFTAR' => 3,
                'BEACUKAI' => 4,
                'EXIBOX' => 5,
                'DUAL SIM' => 6,
                'GARANSI ON' => 7,
                'KREDIT BEACUKAI' => 8,
                'KREDIT EXIBOX' => 9,
                'KREDIT NEW' => 10,
                'KREDIT DUAL SIM' => 11,
                'KREDIT GARANSI' => 12,
                'SRP NEW' => 13,
                'HARGA TOKO NEW' => 14,
                'HARGA ONLINE' => 17,
            ],
        ],
        'IPAD' => [
            'seri_col' => 0,
            'storage_col' => 1,
            'ram_col' => null,
            'harga_nasional' => 3,  // SRP WIFI (harga coret)
            'special_price' => 5,  // HARGA TOKO WIFI (no fallback)
            'carry_seri' => true,
            'kondisi_cols' => [
                'SRP WIFI' => 3,
                'SRP WIFI+CELL' => 4,
                'HARGA TOKO WIFI' => 5,
                'HARGA TOKO WIFI+CELL' => 6,
                'SECOND WIFI' => 7,
                'SECOND WIFI+CELL' => 8,
                'KREDIT NEW WIFI' => 9,
                'KREDIT NEW WIFI+CELL' => 10,
                'KREDIT SECOND WIFI' => 11,
                'KREDIT SECOND WIFI+CELL' => 12,
                'HARGA ONLINE WIFI' => 13,
                'HARGA ONLINE WIFI+CELL' => 14,
            ],
        ],
        'MACBOOK' => [
            'seri_col' => 0,
            'storage_col' => 3,  // INTERNL (SSD) — combined with RAM
            'ram_col' => 2,  // RAM
            'harga_nasional' => 5,  // SRP NEW (harga coret)
            'special_price' => 6,  // CASH TOKO (no fallback)
            'carry_seri' => false,
            'kondisi_cols' => [
                'SRP NEW' => 5,
                'CASH TOKO' => 6,
                'SECOND' => 7,
                'KREDIT NEW' => 8,
                'KREDIT SECOND' => 9,
                'HARGA ONLINE' => 10,
            ],
        ],
        'APPLE WATCH' => [
            'seri_col' => 0,
            'storage_col' => 1,  // SIZE (42MM / 46MM / etc.)
            'ram_col' => null,
            'harga_nasional' => 3,  // SRP NEW (harga coret)
            'special_price' => 4,  // CASH NEW (no fallback)
            'carry_seri' => true,
            'kondisi_cols' => [
                'SRP NEW' => 3,
                'CASH NEW' => 4,
                'SECOND' => 5,
                'KREDIT NEW' => 6,
                'KREDIT SECOND' => 7,
                'HARGA ONLINE' => 8,
            ],
        ],
        'AIRPODS' => [
            'seri_col' => 0,
            'storage_col' => null,
            'ram_col' => null,
            'harga_nasional' => 1,  // SRP NEW (harga coret)
            'special_price' => 2,  // CASH TOKO (no fallback)
            'carry_seri' => false,
            'kondisi_cols' => [
                'SRP NEW' => 1,
                'CASH TOKO' => 2,
                'SECOND' => 3,
                'KREDIT NEW' => 4,
                'KREDIT SECOND' => 5,
                'HARGA ONLINE' => 6,
            ],
        ],
        'APPLE PENCIL' => [
            'seri_col' => 0,
            'storage_col' => null,
            'ram_col' => null,
            'harga_nasional' => 1,  // SRP NEW (harga coret)
            'special_price' => 2,  // CASH TOKO (no fallback)
            'carry_seri' => false,
            'kondisi_cols' => [
                'SRP NEW' => 1,
                'CASH TOKO' => 2,
                'SECOND' => 3,
                'KREDIT NEW' => 4,
                'KREDIT SECOND' => 5,
                'HARGA ONLINE' => 6,
            ],
        ],
    ];

    /** @var null|callable(string): string */
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
        $targetSheets = $sheets
            ? array_values(array_intersect($sheets, self::SHEETS))
            : self::SHEETS;

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
     * Parse CSV into indexed-column rows (header row is skipped).
     *
     * @return array<int, array{cols: array<int, string>, source_row: int}>
     */
    public function parseCsv(string $csv): array
    {
        $lines = preg_split('/\r\n|\n|\r/', trim($csv));
        if (! is_array($lines) || $lines === []) {
            return [];
        }

        array_shift($lines); // skip header row

        $rows = [];
        foreach ($lines as $lineNumber => $line) {
            if (trim((string) $line) === '') {
                continue;
            }
            $values = str_getcsv((string) $line, ',', '"', '\\');
            $cols = array_map(fn ($v) => trim((string) ($v ?? '')), $values);
            $rows[] = [
                'cols' => $cols,
                'source_row' => $lineNumber + 2,
            ];
        }

        return $rows;
    }

    /**
     * @param  array<int, array{cols: array<int, string>, source_row: int}>  $rows
     * @return array{sheet: string, imported: int, skipped: int}
     */
    public function importRows(string $sheetName, array $rows): array
    {
        $cfg = self::SHEET_CONFIG[$sheetName] ?? null;
        if ($cfg === null) {
            return ['sheet' => $sheetName, 'imported' => 0, 'skipped' => count($rows)];
        }

        $now = now();
        $imported = 0;
        $skipped = 0;
        $lastSeri = null;
        $urut = 0;

        foreach ($rows as $row) {
            $cols = $row['cols'];
            $sourceRow = $row['source_row'];

            // Resolve model name (with optional carry-forward)
            $rawSeri = $this->col($cols, $cfg['seri_col']);
            if ($cfg['carry_seri'] && $rawSeri === '') {
                $rawSeri = $lastSeri ?? '';
            } elseif ($rawSeri !== '') {
                $lastSeri = $rawSeri;
            }

            $model = $rawSeri;

            if ($model === '' || $this->looksLikeHeader($model)) {
                $skipped++;

                continue;
            }

            // Storage (optionally combined RAM/SSD for MacBook)
            $storageParts = [];
            if ($cfg['ram_col'] !== null) {
                $ram = $this->col($cols, $cfg['ram_col']);
                if ($ram !== '') {
                    $storageParts[] = $ram;
                }
            }
            if ($cfg['storage_col'] !== null) {
                $storageRaw = $this->col($cols, $cfg['storage_col']);
                if ($storageRaw !== '') {
                    $storageParts[] = $storageRaw;
                }
            }
            $storage = implode('/', $storageParts) ?: null;

            // Main prices — no fallback; "-" stays as null
            $hargaNasional = $this->moneyCol($cols, $cfg['harga_nasional']);
            $specialPrice = $this->moneyCol($cols, $cfg['special_price']);

            // Condition prices — all columns, no fallback
            $hargaKondisi = [];
            foreach ($cfg['kondisi_cols'] ?? [] as $label => $colIdx) {
                $val = $this->moneyCol($cols, $colIdx);
                $hargaKondisi[$label] = $val; // null stored when "-"
            }

            // Skip rows with no model AND no prices at all
            $hasAnyPrice = $hargaNasional !== null || $specialPrice !== null
                || count(array_filter($hargaKondisi, fn ($v) => $v !== null)) > 0;

            if (! $hasAnyPrice) {
                $skipped++;

                continue;
            }

            $urut++;
            $normalizedKey = $this->normalizedKey($sheetName, $model, $storage);

            DB::table('apple_products')->updateOrInsert(
                ['source_sheet' => $sheetName, 'source_row' => $sourceRow],
                [
                    'source_id' => 'AP'.str_pad((string) abs(crc32($sheetName.'|'.$sourceRow)), 10, '0', STR_PAD_LEFT),
                    'urut' => $urut,
                    'model' => $model,
                    'storage' => $storage,
                    'harga_nasional' => $hargaNasional,
                    'special_price' => $specialPrice,
                    'harga_kondisi' => json_encode($hargaKondisi, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'normalized_key' => $normalizedKey,
                    'is_active' => true,
                    'raw_payload' => json_encode($cols, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'imported_at' => $now,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );

            $imported++;
        }

        return ['sheet' => $sheetName, 'imported' => $imported, 'skipped' => $skipped];
    }

    private function col(array $cols, int $index): string
    {
        return trim((string) ($cols[$index] ?? ''));
    }

    private function moneyCol(array $cols, int $index): ?int
    {
        $raw = $this->col($cols, $index);
        if ($raw === '' || $raw === '-') {
            return null;
        }
        $cleaned = preg_replace('/[^0-9]/', '', $raw) ?: '';
        if ($cleaned === '') {
            return null;
        }
        $val = (int) $cleaned;

        return $val > 0 ? $val : null;
    }

    private function looksLikeHeader(string $value): bool
    {
        $upper = strtoupper($value);

        return str_starts_with($upper, 'SERI ') || str_starts_with($upper, 'TYPE ');
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

    private function normalizedKey(string $sheetName, string $model, ?string $storage): string
    {
        $parts = array_filter([$sheetName, $model, $storage], fn ($p) => filled($p));

        return implode('|', array_map(
            fn ($p) => strtoupper(preg_replace('/\s+/', ' ', trim((string) $p)) ?: ''),
            $parts
        ));
    }
}
