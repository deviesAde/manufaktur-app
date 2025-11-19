<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinishedGood extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'unit',
        'stock',
        'min_stock',
        'price',
        'production_cost',
        'description'
    ];

    
    public function getFormattedPriceAttribute()
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    // Relasi ke bahan baku (recipe)
    public function rawMaterials()
    {
        return $this->belongsToMany(RawMaterial::class, 'finished_good_raw_material')
                    ->withPivot('quantity');
    }

    // Alias untuk compatibility
    public function recipe()
    {
        return $this->rawMaterials();
    }

    public function salesOrderItems()
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function productionOrders()
    {
        return $this->hasMany(ProductionOrder::class);
    }

    // Cek apakah perlu produksi (stok rendah)
    public function needsProduction()
    {
        return $this->stock <= $this->min_stock;
    }

    // Hitung biaya produksi berdasarkan recipe
    public function calculateProductionCost($quantity = 1)
    {
        $totalCost = 0;
        foreach ($this->rawMaterials as $rawMaterial) {
            $required = $rawMaterial->pivot->quantity * $quantity;
            // Asumsi harga bahan baku dari rata-rata purchase order
            $avgPrice = $rawMaterial->purchaseOrderItems()->avg('price') ?? 0;
            $totalCost += $required * $avgPrice;
        }
        return $totalCost;
    }
}
