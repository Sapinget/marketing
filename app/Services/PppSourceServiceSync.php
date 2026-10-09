<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PppSourceServiceSync
{
    public static function sync(int $limit = 1000): int
    {
        if (! Schema::connection('ppp_source')->hasTable('services') || ! Schema::hasTable('services')) {
            return 0;
        }

        $rows = DB::connection('ppp_source')->table('services as s')
            ->leftJoin('customer as c', 'c.id', '=', 's.customer_id')
            ->where('s.is_deleted', 0)
            ->orderByDesc('s.updated_at')
            ->limit($limit)
            ->get(['s.*', 'c.name as customer_name', 'c.phone as customer_phone']);

        $statusMap = [
            0 => 'PENDING',
            1 => 'PROSES',
            2 => 'SELESAI',
            3 => 'LUNAS',
            4 => 'BATAL',
        ];

        $count = 0;
        foreach ($rows as $row) {
            $statusRaw = $row->status;
            $statusLabel = is_numeric($statusRaw) ? ($statusMap[(int) $statusRaw] ?? (string) $statusRaw) : (string) $statusRaw;

            DB::table('services')->updateOrInsert(
                ['source_id' => $row->id],
                [
                    'no_service' => $row->invoice_no,
                    'tanggal' => $row->date,
                    'nama_customer' => $row->customer_name ?: $row->customer_id,
                    'wa_customer' => $row->customer_phone,
                    'type_unit' => $row->product,
                    'imei_sn' => $row->imei,
                    'kerusakan' => $row->notes,
                    'status' => $statusLabel,
                    'keterangan' => $row->notes,
                    'total' => $row->total,
                    'handle_by' => $row->handle_by,
                    'raw_payload' => json_encode((array) $row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'source_updated_at' => $row->updated_at,
                    'imported_at' => now(),
                    'updated_at' => now(),
                ]
            );
            $count++;
        }

        return $count;
    }
}
