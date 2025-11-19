<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RawMaterial extends Model
{
    protected $fillable = ['name', 'stock', 'unit', 'min_stock'];

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);    }

    public function productionOrderItems()
    {
        return $this->hasMany(ProductionOrderItem::class);
    }

    public function finishedGoods()
    {
        return $this->belongsToMany(FinishedGood::class, 'finished_good_raw_material')
                    ->withPivot('quantity');
    }

    // Accessor untuk status stok
    public function getStockStatusAttribute()
    {
        if ($this->stock <= 0) {
            return 'Habis';
        } elseif ($this->stock <= $this->min_stock) {
            return 'Rendah';
        } else {
            return 'Aman';
        }
    }

    // Accessor untuk warna status stok
    public function getStockStatusColorAttribute()
    {
        return match($this->stock_status) {
            'Habis' => 'danger',
            'Rendah' => 'warning',
            'Aman' => 'success',
            default => 'gray'
        };
    }

    // Cek apakah stok rendah
    public function getIsLowStockAttribute()
    {
        return $this->stock <= $this->min_stock;
    }

    // Cek apakah stok habis
    public function getIsOutOfStockAttribute()
    {
        return $this->stock <= 0;
    }

    // Scope untuk bahan baku dengan stok rendah
    public function scopeLowStock($query)
    {
        return $query->where('stock', '<=', DB::raw('min_stock'));
    }

    // Scope untuk bahan baku habis
    public function scopeOutOfStock($query)
    {
        return $query->where('stock', '<=', 0);
    }

    // Scope untuk bahan baku aman
    public function scopeAdequateStock($query)
    {
        return $query->where('stock', '>', DB::raw('min_stock'));
    }

    // Hitung total kebutuhan bahan baku dari production orders yang pending
    public function getPendingProductionQuantityAttribute()
    {
        return $this->productionOrderItems()
            ->whereHas('productionOrder', function ($query) {
                $query->whereIn('status', ['pending', 'in_progress']);
            })
            ->sum('quantity_used');
    }

    // Hitung sisa stok yang available (setelah dikurangi kebutuhan production)
    public function getAvailableStockAttribute()
    {
        return $this->stock - $this->pending_production_quantity;
    }
}
