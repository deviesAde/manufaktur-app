<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrderItem extends Model
{
    protected $fillable = [
        'sales_order_id',
        'finished_good_id',
        'quantity',
        'price',
        'subtotal'
    ];

    protected static function boot()
    {
        parent::boot();

        // Ketika item dibuat, kurangi stok finished good
        static::created(function ($item) {
            $finishedGood = FinishedGood::find($item->finished_good_id);
            if ($finishedGood) {
                // Validasi stok cukup
                if ($finishedGood->stock < $item->quantity) {
                    // Rollback creation jika stok tidak cukup
                    $item->delete();
                    throw new \Exception("Stok {$finishedGood->name} tidak mencukupi. Stok tersedia: {$finishedGood->stock}, Dibutuhkan: {$item->quantity}");
                }
                // Kurangi stok
                $finishedGood->decrement('stock', $item->quantity);
            }
        });

        // Ketika item diupdate, adjust stok
        static::updating(function ($item) {
            $oldQuantity = $item->getOriginal('quantity');
            $newQuantity = $item->quantity;
            $finishedGoodId = $item->getOriginal('finished_good_id');

            $finishedGood = FinishedGood::find($finishedGoodId);

            if ($finishedGood) {
                // Kembalikan stok lama dulu
                $finishedGood->increment('stock', $oldQuantity);

                // Cek stok untuk quantity baru
                if ($finishedGood->stock < $newQuantity) {
                    // Jika tidak cukup, kembalikan ke semula
                    $finishedGood->decrement('stock', $oldQuantity);
                    throw new \Exception("Stok {$finishedGood->name} tidak mencukupi. Stok tersedia: {$finishedGood->stock}, Dibutuhkan: {$newQuantity}");
                }

                // Kurangi stok dengan quantity baru
                $finishedGood->decrement('stock', $newQuantity);
            }
        });

        // Ketika item dihapus, kembalikan stok
        static::deleted(function ($item) {
            $finishedGood = FinishedGood::find($item->finished_good_id);
            if ($finishedGood) {
                $finishedGood->increment('stock', $item->quantity);
            }
        });
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function finishedGood()
    {
        return $this->belongsTo(FinishedGood::class);
    }

    // Backwards-compatible alias used elsewhere in the codebase
    public function finishedProduct()
    {
        return $this->finishedGood();
    }
}
