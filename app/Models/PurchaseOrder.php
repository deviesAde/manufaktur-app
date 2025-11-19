<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'po_number',
        'supplier_id',
        'order_date',
        'expected_date',
        'status',
        'total_cost',
        'notes'
    ];

    // Status constants
    const STATUS_MENUNGGU = 'Menunggu';
    const STATUS_DIKIRIM = 'Dikirim';
    const STATUS_DITERIMA = 'Diterima';
    const STATUS_DIBATALKAN = 'Dibatalkan';

    // Relationship dengan Supplier
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // Relationship dengan Items
    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    protected static function boot()
    {
        parent::boot();

        // 1. Auto generate PO number
        static::creating(function ($purchaseOrder) {
            if (!$purchaseOrder->po_number) {
                $purchaseOrder->po_number = 'PO-' . date('Ymd') . '-' . str_pad(self::count() + 1, 4, '0', STR_PAD_LEFT);
            }
        });

        // 2. Auto calculate total cost ketika items diupdate
        static::saved(function ($purchaseOrder) {
            $total = $purchaseOrder->items()->sum('subtotal');
            if ($purchaseOrder->total_cost != $total) {
                $purchaseOrder->update(['total_cost' => $total]);
            }
        });

        // 3. Auto update stok raw material ketika PO diterima
        static::updated(function ($purchaseOrder) {
            // Jika status berubah menjadi DITERIMA
            if ($purchaseOrder->status === self::STATUS_DITERIMA &&
                $purchaseOrder->getOriginal('status') !== self::STATUS_DITERIMA) {

                foreach ($purchaseOrder->items as $item) {
                    $rawMaterial = $item->rawMaterial;
                    $rawMaterial->increment('stock', $item->quantity);

                    // Log the stock addition
                    Log::info("Stock added for {$rawMaterial->name}: +{$item->quantity}");
                }
            }

            // Jika status berubah menjadi DIBATALKAN (dari DITERIMA)
            if ($purchaseOrder->status === self::STATUS_DIBATALKAN &&
                $purchaseOrder->getOriginal('status') === self::STATUS_DITERIMA) {

                foreach ($purchaseOrder->items as $item) {
                    $rawMaterial = $item->rawMaterial;
                    if ($rawMaterial->stock >= $item->quantity) {
                        $rawMaterial->decrement('stock', $item->quantity);
                    }
                }
            }
        });
    }

    // Accessor untuk status color
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            self::STATUS_MENUNGGU => 'gray',
            self::STATUS_DIKIRIM => 'blue',
            self::STATUS_DITERIMA => 'green',
            self::STATUS_DIBATALKAN => 'red',
            default => 'gray'
        };
    }

    // Cek apakah bisa di-edit
    public function getCanEditAttribute()
    {
        return in_array($this->status, [self::STATUS_MENUNGGU, self::STATUS_DIKIRIM]);
    }

    // Cek apakah bisa di-cancel
    public function getCanCancelAttribute()
    {
        return $this->status !== self::STATUS_DITERIMA && $this->status !== self::STATUS_DIBATALKAN;
    }

    // Hitung total items
    public function getTotalItemsAttribute()
    {
        return $this->items()->count();
    }
}
