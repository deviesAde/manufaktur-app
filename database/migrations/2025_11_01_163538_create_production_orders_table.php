<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_orders', function (Blueprint $table) {
            $table->id();
            $table->string('production_code')->unique();
            $table->foreignId('finished_good_id')->constrained()->cascadeOnDelete(); // TAMBAH INI
            $table->foreignId('sales_order_id')->nullable()->constrained()->cascadeOnDelete();
            $table->integer('quantity'); // TAMBAH: jumlah yang akan diproduksi
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_orders');
    }
};
