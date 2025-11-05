<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionOrder extends Model
{
    protected $fillable = [
        'production_code',
        'sales_order_id',
        'start_date',
        'end_date',
        'status',
        'notes'
    ];

    public function items()
    {
        return $this->hasMany(ProductionOrderItem::class);
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }
}
