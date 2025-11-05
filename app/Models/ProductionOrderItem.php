<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionOrderItem extends Model
{
    protected $fillable = [
        'production_order_id',
        'raw_material_id',
        'quantity_used'
    ];

    public function productionOrder()
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class);
    }

    protected static function booted()
    {
        static::created(function ($item) {
            $raw = $item->rawMaterial;
            if ($raw) {
                $raw->stock -= $item->quantity_used;
                $raw->stock = max(0, $raw->stock);
                $raw->save();
            }
        });

        static::updated(function ($item) {
            $raw = $item->rawMaterial;
            if ($raw && $item->getOriginal('quantity_used') !== $item->quantity_used) {
                $diff = $item->quantity_used - $item->getOriginal('quantity_used');
                $raw->stock -= $diff;
                $raw->stock = max(0, $raw->stock);
                $raw->save();
            }
        });

        static::deleted(function ($item) {
            $raw = $item->rawMaterial;
            if ($raw) {
                $raw->stock += $item->quantity_used;
                $raw->save();
            }
        });
    }
}
