<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'supplied_materials',
        'notes',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    // Relationship dengan Purchase Orders
    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    // Accessor untuk materials yang disupply (array)
    public function getSuppliedMaterialsListAttribute()
    {
        return $this->supplied_materials ? explode(',', $this->supplied_materials) : [];
    }

       public static function generatePoNumber()
    {
        $prefix = 'PO';
        $date = now()->format('Ymd');

        do {
            $random = Str::upper(Str::random(6));
            $poNumber = "{$prefix}-{$date}-{$random}";
        } while (static::where('po_number', $poNumber)->exists());

        return $poNumber;
    }


    // Mutator untuk menyimpan materials sebagai string
    public function setSuppliedMaterialsAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['supplied_materials'] = implode(',', $value);
        } else {
            $this->attributes['supplied_materials'] = $value;
        }
    }

    // Scope untuk supplier aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Hitung total PO dari supplier ini
    public function getTotalOrdersAttribute()
    {
        return $this->purchaseOrders()->count();
    }

    // Hitung total nilai pembelian
    public function getTotalPurchaseValueAttribute()
    {
        return $this->purchaseOrders()->sum('total_cost');
    }
}
