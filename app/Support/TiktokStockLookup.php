<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Looks up stock and online price in db_analis (connection `ppp_source`) for
 * marketplace template rows. Rows carry no internal SKU, so a variant is matched
 * on model + color + storage (+ RAM when the variation has it), "BARU" condition only.
 *
 * Stock: units available in Gudang Monang maning.
 * Price: `selling_price_online` is stored per unit, so one variant can hold several.
 * Rule: highest price among the units in stock; with no stock, the price of the most
 * recently updated unit that has one. Empty / 0 prices never produce a price.
 */
class TiktokStockLookup
{
    private const CONDITION_BARU = 2;

    /** TikTok stock is sold from Gudang Toko Monang maning only. */
    private const WAREHOUSE_ID = 3;

    /**
     * @param  array<int, array{product_name?: mixed, variation_value?: mixed}>  $items
     * @return array<int, array{stock: int|null, price: int|null, price_source: string|null, prices: array<int, int>, reason?: string}> same keys as $items
     */
    public function lookup(array $items): array
    {
        $models = $this->modelIndex($this->stockIndex());
        $result = [];

        foreach ($items as $i => $item) {
            $model = $this->modelFromTitle((string) ($item['product_name'] ?? ''));
            [$color, $storage, $ram] = $this->parseVariation((string) ($item['variation_value'] ?? ''));
            $none = ['stock' => null, 'price' => null, 'price_source' => null, 'prices' => []];

            if ($model === '' || ! isset($models[$model])) {
                $result[$i] = $none + ['reason' => 'Model tidak ditemukan di db_analis'];

                continue;
            }

            $total = 0;
            $found = false;
            $inStock = [];
            $latest = null;
            foreach ($models[$model] as $entry) {
                if ($entry['color'] !== $color) {
                    continue;
                }
                if ($storage !== null && $entry['storage'] !== $storage) {
                    continue;
                }
                if ($ram !== null && $entry['ram'] !== $ram) {
                    continue;
                }
                $total += $entry['qty'];
                $inStock = array_merge($inStock, $entry['stock_prices']);
                $latest ??= $entry['latest_price'];
                $found = true;
            }

            if (! $found) {
                $result[$i] = $none + ['reason' => 'Varian (warna/storage) tidak ditemukan'];

                continue;
            }

            $inStock = array_values(array_unique($inStock));
            sort($inStock);
            $price = $inStock !== [] ? max($inStock) : $latest;

            $result[$i] = [
                'stock' => max(0, $total),
                'price' => $price,
                'price_source' => $price === null ? null : ($inStock !== [] ? 'stok' : 'terbaru'),
                'prices' => $inStock,
            ];
        }

        return $result;
    }

    /**
     * Built from the base tables on purpose: the *_v tables in db_analis are stale snapshots.
     *
     * @return array<int, array{model: string, color: string, storage: int|null, ram: int|null, qty: int, stock_prices: array<int, int>, latest_price: int|null}>
     */
    private function stockIndex(): array
    {
        $inStock = 'ps.warehouse_id = '.self::WAREHOUSE_ID.' AND ps.isAvailable = 1 AND ps.is_keep = 0 AND ps.quantity > 0';

        $rows = DB::connection('ppp_source')->table('product_stock as ps')
            ->join('product as p', 'p.id', '=', 'ps.product_id')
            ->leftJoin('product_storage as st', 'st.id', '=', 'ps.product_storage_id')
            ->leftJoin('product_storage as rm', 'rm.id', '=', 'ps.ram_id')
            ->leftJoin('colors as c', 'c.id', '=', 'ps.colors_id')
            ->where('ps.is_deleted', 0)
            ->where('p.is_deleted', 0)
            ->where('p.product_condition_id', self::CONDITION_BARU)
            ->groupBy('p.name', 'c.name', 'ps.colors', 'st.storage', 'rm.storage')
            ->selectRaw("p.name as product_name, COALESCE(c.name, ps.colors) as color_name, st.storage, rm.storage as ram,
                SUM(CASE WHEN {$inStock} THEN ps.quantity ELSE 0 END) as qty,
                GROUP_CONCAT(DISTINCT CASE WHEN {$inStock} AND ps.selling_price_online > 0 THEN ps.selling_price_online END) as stock_prices,
                SUBSTRING_INDEX(GROUP_CONCAT(CASE WHEN ps.selling_price_online > 0 THEN ps.selling_price_online END ORDER BY ps.updated_at DESC), ',', 1) as latest_price")
            ->get();

        return $rows->map(fn ($row) => [
            'model' => $this->norm((string) $row->product_name),
            'color' => $this->normColor((string) $row->color_name),
            'storage' => $row->storage !== null ? (int) $row->storage : null,
            'ram' => $row->ram !== null ? (int) $row->ram : null,
            'qty' => (int) $row->qty,
            'stock_prices' => $row->stock_prices === null || $row->stock_prices === '' ? [] : array_map('intval', explode(',', $row->stock_prices)),
            'latest_price' => $row->latest_price === null || $row->latest_price === '' ? null : (int) $row->latest_price,
        ])->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $stock
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function modelIndex(array $stock): array
    {
        $models = [];
        foreach ($stock as $entry) {
            $models[$entry['model']][] = $entry;
        }

        return $models;
    }

    private function modelFromTitle(string $title): string
    {
        $title = preg_replace('/^\s*\[[^\]]*\]\s*/', '', $title);
        $title = preg_replace('/^\s*NEW\s+/i', '', (string) $title);
        $title = preg_replace('/\s+GARANSI\s+RESMI.*$/i', '', (string) $title);
        $title = preg_replace('/\s+GIFT\s?BOX\b/i', '', (string) $title);

        return $this->norm((string) $title);
    }

    /**
     * "ULTRAMARINE, 128GB", "MIST TITANIUM, 8GB/256GB", "256GB, SILVER", "Default".
     *
     * @return array{0: string, 1: int|null, 2: int|null} color, storage GB, ram GB
     */
    private function parseVariation(string $value): array
    {
        $color = '';
        $storage = null;
        $ram = null;

        foreach (explode(',', $value) as $part) {
            $part = trim($part);
            if (preg_match('/^(\d+)\s*GB\s*\/\s*(\d+)\s*(GB|TB)$/i', $part, $m)) {
                $ram = (int) $m[1];
                $storage = $this->toGb((int) $m[2], $m[3]);
            } elseif (preg_match('/^(\d+)\s*(GB|TB)$/i', $part, $m)) {
                $storage = $this->toGb((int) $m[1], $m[2]);
            } elseif (strcasecmp($part, 'Default') !== 0) {
                $color = $this->normColor($part);
            }
        }

        return [$color, $storage, $ram];
    }

    /** DB stores 1TB as 1000 and 2TB as 2000. */
    private function toGb(int $amount, string $unit): int
    {
        return strtoupper($unit) === 'TB' ? $amount * 1000 : $amount;
    }

    private function norm(string $value): string
    {
        return trim((string) preg_replace('/\s+/', ' ', strtoupper($value)));
    }

    private function normColor(string $value): string
    {
        return str_replace('DESSERT', 'DESERT', $this->norm($value));
    }
}
