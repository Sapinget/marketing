<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_vendor_inventory', function (Blueprint $table) {
            $table->id();
            $table->string('source_id')->unique();
            $table->string('vendor')->nullable();
            $table->string('brand')->nullable();
            $table->string('seri')->nullable();
            $table->string('imei')->nullable();
            $table->integer('quantity')->default(1);
            $table->string('condition')->nullable();
            $table->date('purchase_date')->nullable();
            $table->text('notes')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('created_by_user_id')->nullable();
            $table->unsignedBigInteger('updated_by_user_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_vendor_inventory');
    }
};
