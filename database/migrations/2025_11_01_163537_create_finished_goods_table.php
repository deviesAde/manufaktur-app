<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finished_goods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('unit');
            $table->integer('stock')->default(0);
            $table->integer('min_stock')->default(0); // TAMBAH: stok minimum
            $table->decimal('price', 12, 2);
            $table->decimal('production_cost', 12, 2)->default(0); // TAMBAH: biaya produksi
            $table->text('description')->nullable(); // TAMBAH: deskripsi
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finished_goods');
    }
};
