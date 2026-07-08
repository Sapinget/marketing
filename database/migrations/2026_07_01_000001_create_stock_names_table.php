<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_names', function (Blueprint $table) {
            $table->id();
            $table->string('source_id')->nullable()->unique();
            $table->string('kategori')->nullable();
            $table->string('brand')->nullable();
            $table->string('seri')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();

            $table->unique(['kategori', 'brand', 'seri']);
            $table->index('kategori');
            $table->index('brand');
            $table->index('seri');
        });

        $legacyRows = DB::table('marketing_excel_rows')
            ->where('sheet_name', 'Nama_Stock')
            ->orderBy('row_number')
            ->get(['payload', 'imported_at', 'created_at', 'updated_at']);

        foreach ($legacyRows as $row) {
            $payload = json_decode($row->payload, true);

            if (! is_array($payload)) {
                continue;
            }

            $kategori = preg_replace('/\s+/', ' ', strtoupper(trim((string) ($payload['KATEGORI'] ?? ''))));
            $brand = preg_replace('/\s+/', ' ', strtoupper(trim((string) ($payload['BRAND'] ?? ''))));
            $seri = preg_replace('/\s+/', ' ', strtoupper(trim((string) ($payload['SERI'] ?? ''))));

            DB::table('stock_names')->updateOrInsert(
                [
                    'kategori' => $kategori,
                    'brand' => $brand,
                    'seri' => $seri,
                ],
                [
                    'source_id' => filled($payload['ID'] ?? null) ? (string) $payload['ID'] : null,
                    'imported_at' => $row->imported_at,
                    'created_at' => $row->created_at ?? now(),
                    'updated_at' => $row->updated_at ?? now(),
                ],
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_names');
    }
};
