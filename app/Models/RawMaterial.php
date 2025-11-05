<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RawMaterial extends Model
{
    protected $fillable = ['name', 'stock', 'unit'];

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function productionOrderItems()
    {
        return $this->hasMany(ProductionOrderItem::class);
    }

    public function finishedGoods()
{
    return $this->belongsToMany(FinishedGood::class, 'finished_good_raw_material')
                ->withPivot('quantity');
}

}
