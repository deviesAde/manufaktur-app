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

        // Auto calculate subtotal sebelum save
        static::saving(function ($item) {
            $item->subtotal = $item->quantity * $item->price;
        });

        // Trigger update total amount di Sales Order
        static::saved(function ($item) {
            $item->salesOrder->touch();
        });

        static::deleted(function ($item) {
            $item->salesOrder->touch();
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

    public function finishedProduct()
    {
        return $this->finishedGood();
    }

    // Accessor untuk product name
    public function getProductNameAttribute()
    {
        return $this->finishedGood ? $this->finishedGood->name : 'N/A';
    }

    // Accessor untuk brand
    public function getBrandAttribute()
    {
        return $this->finishedGood ? $this->finishedGood->brand : null;
    }

    // Cek apakah stok cukup untuk item ini
    public function getIsStockSufficientAttribute()
    {
        return $this->finishedGood && $this->finishedGood->stock >= $this->quantity;
    }
}
