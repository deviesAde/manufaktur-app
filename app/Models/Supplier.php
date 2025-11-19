<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
