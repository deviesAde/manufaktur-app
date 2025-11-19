<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'raw_material_id',
        'quantity',
        'price',
        'subtotal'
    ];

    // Relationship dengan Purchase Order
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    // Relationship dengan Raw Material
    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class);
    }

    protected static function boot()
    {
        parent::boot();

        // Auto calculate subtotal sebelum save
        static::saving(function ($item) {
            $item->subtotal = $item->quantity * $item->price;
        });

        // Trigger update total cost di Purchase Order ketika item diupdate
        static::saved(function ($item) {
            $item->purchaseOrder->touch(); // Ini akan trigger saved event di PurchaseOrder
        });

        static::deleted(function ($item) {
            $item->purchaseOrder->touch(); // Ini akan trigger saved event di PurchaseOrder
        });
    }

    // Accessor untuk nama bahan baku
    public function getMaterialNameAttribute()
    {
        return $this->rawMaterial ? $this->rawMaterial->name : 'N/A';
    }

    // Accessor untuk unit bahan baku
    public function getMaterialUnitAttribute()
    {
        return $this->rawMaterial ? $this->rawMaterial->unit : 'N/A';
    }
}
