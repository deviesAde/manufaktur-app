<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
   protected $fillable = ['customer_name', 'order_date', 'status', 'delivery_status'];


    public function items()
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function productionOrders()
    {
        return $this->hasMany(ProductionOrder::class);
    }
}
