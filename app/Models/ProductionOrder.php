<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionOrder extends Model
{
    protected $fillable = [
        'production_code',
        'finished_good_id', // DITAMBAH
        'sales_order_id',
        'quantity', // DITAMBAH: jumlah produksi
        'start_date',
        'end_date',
        'status',
        'notes'
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    public function items()
    {
        return $this->hasMany(ProductionOrderItem::class);
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function finishedGood() // DITAMBAH
    {
        return $this->belongsTo(FinishedGood::class);
    }

    protected static function boot()
    {
        parent::boot();

        // Auto generate production code
        static::creating(function ($productionOrder) {
            if (!$productionOrder->production_code) {
                $productionOrder->production_code = 'PROD-' . date('Ymd') . '-' . str_pad(self::count() + 1, 4, '0', STR_PAD_LEFT);
            }
        });

        // Auto update stok ketika production completed
        static::updated(function ($productionOrder) {
            // Jika production completed
            if ($productionOrder->status === self::STATUS_COMPLETED &&
                $productionOrder->getOriginal('status') !== self::STATUS_COMPLETED) {


                foreach ($productionOrder->items as $item) {
                    $rawMaterial = $item->rawMaterial;
                    if ($rawMaterial->stock >= $item->quantity_used) {
                        $rawMaterial->decrement('stock', $item->quantity_used);
                    } else {
                        throw new \Exception("Stok {$rawMaterial->name} tidak cukup");
                    }
                }

                // 2. Tambah stok finished good
                $productionOrder->finishedGood->increment('stock', $productionOrder->quantity);
            }

            // Jika production dibatalkan (dari completed)
            if ($productionOrder->status === self::STATUS_CANCELLED &&
                $productionOrder->getOriginal('status') === self::STATUS_COMPLETED) {

                // 1. Kembalikan stok raw materials
                foreach ($productionOrder->items as $item) {
                    $rawMaterial = $item->rawMaterial;
                    $rawMaterial->increment('stock', $item->quantity_used);
                }

                // 2. Kurangi stok finished good
                if ($productionOrder->finishedGood->stock >= $productionOrder->quantity) {
                    $productionOrder->finishedGood->decrement('stock', $productionOrder->quantity);
                }
            }
        });
    }

    // Cek apakah bahan baku cukup untuk produksi
    public function canProduce()
    {
        foreach ($this->items as $item) {
            if ($item->rawMaterial->stock < $item->quantity_used) {
                return false;
            }
        }
        return true;
    }

    public function getCanEditAttribute()
{
    return in_array($this->status, ['pending', 'in_progress']);
}
}
