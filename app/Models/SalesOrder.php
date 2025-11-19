<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    protected $fillable = [
        // HAPUS: 'brand'
        'customer_name',
        'order_date',
        'status',
        'total_amount',
        'notes'
    ];

    // HAPUS: brand constants

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    public function items()
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function productionOrders()
    {
        return $this->hasMany(ProductionOrder::class);
    }

    protected static function boot()
    {
        parent::boot();

        // Auto calculate total amount
        static::saved(function ($salesOrder) {
            $total = $salesOrder->items()->sum('subtotal');
            if ($salesOrder->total_amount != $total) {
                $salesOrder->update(['total_amount' => $total]);
            }
        });

        // Auto update stok ketika sales order completed
        static::updated(function ($salesOrder) {
            // Jika sales order completed, kurangi stok finished goods
            if ($salesOrder->status === self::STATUS_COMPLETED &&
                $salesOrder->getOriginal('status') !== self::STATUS_COMPLETED) {

                foreach ($salesOrder->items as $item) {
                    $finishedGood = $item->finishedGood;
                    if ($finishedGood && $finishedGood->stock >= $item->quantity) {
                        $finishedGood->decrement('stock', $item->quantity);
                    } else {
                        throw new \Exception("Stok {$finishedGood->name} tidak cukup untuk memenuhi pesanan");
                    }
                }
            }

            // Jika sales order dibatalkan (dari completed), kembalikan stok
            if ($salesOrder->status === self::STATUS_CANCELLED &&
                $salesOrder->getOriginal('status') === self::STATUS_COMPLETED) {

                foreach ($salesOrder->items as $item) {
                    $finishedGood = $item->finishedGood;
                    if ($finishedGood) {
                        $finishedGood->increment('stock', $item->quantity);
                    }
                }
            }
        });
    }

    // Accessor untuk status color
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'gray',
            self::STATUS_PROCESSING => 'blue',
            self::STATUS_COMPLETED => 'green',
            self::STATUS_CANCELLED => 'red',
            default => 'gray'
        };
    }

    // Cek apakah bisa diproses (stok cukup)
    public function canProcess()
    {
        foreach ($this->items as $item) {
            if (!$item->finishedGood || $item->finishedGood->stock < $item->quantity) {
                return false;
            }
        }
        return true;
    }

    public function getCanEditAttribute()
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PROCESSING]);
    }

    public function getCanProcessAttribute()
    {
        return $this->status === self::STATUS_PENDING && $this->canProcess();
    }
}
