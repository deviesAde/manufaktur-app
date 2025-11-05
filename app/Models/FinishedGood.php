<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinishedGood extends Model
{
    use HasFactory;

    protected $table = 'finished_goods';

    protected $fillable = [
        'name',
        'unit',
        'stock',
        'price',
    ];

    public function getFormattedPriceAttribute()
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    // Relasi ke bahan baku (raw materials) melalui pivot table
    public function recipe()
    {
        return $this->belongsToMany(RawMaterial::class, 'finished_good_raw_material')
                    ->withPivot('quantity'); // jumlah bahan yang dibutuhkan untuk 1 unit produk
    }

    public function salesOrderItems()
    {
        return $this->hasMany(SalesOrderItem::class, 'finished_good_id');
    }

    // Alias untuk compatibility dengan kode lama
    public function salesItems()
    {
        return $this->salesOrderItems();
    }
}
