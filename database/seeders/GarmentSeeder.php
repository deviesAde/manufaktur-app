<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Supplier;
use App\Models\RawMaterial;
use App\Models\FinishedGood;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\ProductionOrder;
use App\Models\ProductionOrderItem;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class GarmentSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Clear existing data
        ProductionOrderItem::truncate();
        ProductionOrder::truncate();
        SalesOrderItem::truncate();
        SalesOrder::truncate();
        PurchaseOrderItem::truncate();
        PurchaseOrder::truncate();
        FinishedGood::truncate();
        RawMaterial::truncate();
        Supplier::truncate();
        User::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1. Create Admin User
        User::create([
            'name' => 'Admin Garment',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
        ]);

        // 2. Create Suppliers
        $suppliers = [
            [
                'name' => 'PT Kain Nusantara',
                'contact_person' => 'Budi Santoso',
                'phone' => '+62 812-3456-7890',
                'email' => 'budi@kainnusantara.com',
                'address' => 'Jl. Tekstil No. 123, Surabaya',
                'supplied_materials' => 'Kain Katun,Kain Sutra,Kain Linen,Benang',
                'notes' => 'Supplier kain utama',
                'is_active' => true,
            ],
            [
                'name' => 'CV Aksesori Fashion',
                'contact_person' => 'Sari Dewi',
                'phone' => '+62 813-4567-8901',
                'email' => 'sari@aksesorifashion.com',
                'address' => 'Jl. Aksesoris No. 45, Malang',
                'supplied_materials' => 'Resleting,Kancing,Manik-manik,Renda',
                'notes' => 'Supplier aksesoris berkualitas',
                'is_active' => true,
            ],
            [
                'name' => 'UD Bahan Jahit Maju',
                'contact_person' => 'Joko Prasetyo',
                'phone' => '+62 814-5678-9012',
                'email' => 'joko@bahanjahit.com',
                'address' => 'Jl. Industri No. 78, Jember',
                'supplied_materials' => 'Dakron,Kapas,Tali,Karet',
                'notes' => 'Supplier bahan pelengkap',
                'is_active' => true,
            ]
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }

        // 3. Create Raw Materials
        $rawMaterials = [
            // Kain
            [
                'name' => 'Kain Katun Prima',
                'unit' => 'meter',
                'stock' => 200,
                'min_stock' => 50,
            ],
            [
                'name' => 'Kain Linen',
                'unit' => 'meter',
                'stock' => 80,
                'min_stock' => 20,
            ],
            [
                'name' => 'Kain Sutra',
                'unit' => 'meter',
                'stock' => 40,
                'min_stock' => 10,
            ],
            [
                'name' => 'Kain Denim',
                'unit' => 'meter',
                'stock' => 60,
                'min_stock' => 15,
            ],
            // Aksesoris
            [
                'name' => 'Resleting Metal 50cm',
                'unit' => 'pcs',
                'stock' => 300,
                'min_stock' => 100,
            ],
            [
                'name' => 'Kancing Baju Plastik',
                'unit' => 'pcs',
                'stock' => 800,
                'min_stock' => 200,
            ],
            [
                'name' => 'Benang Jahit Hitam',
                'unit' => 'roll',
                'stock' => 50,
                'min_stock' => 15,
            ],
            [
                'name' => 'Benang Jahit Putih',
                'unit' => 'roll',
                'stock' => 45,
                'min_stock' => 15,
            ],
            [
                'name' => 'Tali Serut',
                'unit' => 'meter',
                'stock' => 120,
                'min_stock' => 30,
            ]
        ];

        foreach ($rawMaterials as $material) {
            RawMaterial::create($material);
        }

        // 4. Create Finished Goods (Produk Baju Daerah Tapal Kuda)
        $finishedGoods = [
            [
                'name' => 'Baju Koko Pria',
                'unit' => 'pcs',
                'stock' => 25,
                'min_stock' => 10,
                'price' => 85000,
                'production_cost' => 45000,
                'description' => 'Baju koko pria dengan bahan katun nyaman',
            ],
            [
                'name' => 'Kebaya Modern',
                'unit' => 'pcs',
                'stock' => 15,
                'min_stock' => 6,
                'price' => 185000,
                'production_cost' => 95000,
                'description' => 'Kebaya dengan desain modern dan elegan',
            ],
            [
                'name' => 'Kaos Oblong',
                'unit' => 'pcs',
                'stock' => 50,
                'min_stock' => 20,
                'price' => 45000,
                'production_cost' => 22000,
                'description' => 'Kaos oblong basic berbagai ukuran',
            ],
            [
                'name' => 'Kemeja Flanel',
                'unit' => 'pcs',
                'stock' => 18,
                'min_stock' => 8,
                'price' => 120000,
                'production_cost' => 65000,
                'description' => 'Kemeja flanel hangat untuk cuaca dingin',
            ],
            [
                'name' => 'Celana Jeans',
                'unit' => 'pcs',
                'stock' => 30,
                'min_stock' => 12,
                'price' => 150000,
                'production_cost' => 75000,
                'description' => 'Celana jeans denim berkualitas',
            ],
            [
                'name' => 'Blus Wanita',
                'unit' => 'pcs',
                'stock' => 22,
                'min_stock' => 9,
                'price' => 95000,
                'production_cost' => 48000,
                'description' => 'Blus wanita dengan potongan trendy',
            ],
            [
                'name' => 'Jas Almamater',
                'unit' => 'pcs',
                'stock' => 35,
                'min_stock' => 15,
                'price' => 75000,
                'production_cost' => 38000,
                'description' => 'Jas almamater dengan bordir logo',
            ],
            [
                'name' => 'Seragam Sekolah',
                'unit' => 'set',
                'stock' => 40,
                'min_stock' => 18,
                'price' => 110000,
                'production_cost' => 55000,
                'description' => 'Set seragam sekolah lengkap',
            ]
        ];

        foreach ($finishedGoods as $product) {
            FinishedGood::create($product);
        }

        // 5. Create Recipe (Resep Produksi)
        $recipes = [
            // Baju Koko Pria
            'Baju Koko Pria' => [
                'Kain Katun Prima' => 2.2,
                'Benang Jahit Hitam' => 0.3,
                'Kancing Baju Plastik' => 5,
            ],
            // Kebaya Modern
            'Kebaya Modern' => [
                'Kain Sutra' => 2.8,
                'Benang Jahit Putih' => 0.4,
                'Kancing Baju Plastik' => 8,
                'Resleting Metal 50cm' => 1,
            ],
            // Kaos Oblong
            'Kaos Oblong' => [
                'Kain Katun Prima' => 1.5,
                'Benang Jahit Hitam' => 0.2,
            ],
            // Kemeja Flanel
            'Kemeja Flanel' => [
                'Kain Linen' => 2.5,
                'Benang Jahit Putih' => 0.35,
                'Kancing Baju Plastik' => 6,
                'Resleting Metal 50cm' => 1,
            ],
            // Celana Jeans
            'Celana Jeans' => [
                'Kain Denim' => 1.8,
                'Benang Jahit Hitam' => 0.4,
                'Resleting Metal 50cm' => 1,
                'Tali Serut' => 1.2,
            ],
            // Blus Wanita
            'Blus Wanita' => [
                'Kain Katun Prima' => 1.8,
                'Benang Jahit Putih' => 0.25,
                'Kancing Baju Plastik' => 4,
            ]
        ];

        foreach ($recipes as $productName => $materials) {
            $product = FinishedGood::where('name', $productName)->first();
            if ($product) {
                foreach ($materials as $materialName => $quantity) {
                    $material = RawMaterial::where('name', $materialName)->first();
                    if ($material) {
                        $product->rawMaterials()->attach($material->id, ['quantity' => $quantity]);
                    }
                }
            }
        }

        // 6. Create Purchase Orders
        $purchaseOrders = [
            [
                'supplier_id' => 1,
                'order_date' => now()->subDays(15),
                'expected_date' => now()->subDays(5),
                'status' => 'Diterima',
                'notes' => 'Pembelian kain untuk produksi stock',
            ],
            [
                'supplier_id' => 2,
                'order_date' => now()->subDays(8),
                'expected_date' => now()->addDays(2),
                'status' => 'Dikirim',
                'notes' => 'Pembelian aksesoris dan perlengkapan jahit',
            ],
            [
                'supplier_id' => 3,
                'order_date' => now()->subDays(3),
                'expected_date' => now()->addDays(7),
                'status' => 'Menunggu',
                'notes' => 'Pembelian bahan pelengkap produksi',
            ]
        ];

        foreach ($purchaseOrders as $index => $poData) {
            $poData['po_number'] = 'PO-' . now()->format('Ymd') . '-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
            $po = PurchaseOrder::create($poData);

            // Add items to PO based on supplier
            if ($po->supplier_id == 1) { // Supplier kain
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'raw_material_id' => 1, // Kain Katun Prima
                    'quantity' => 200,
                    'price' => 32000,
                    'subtotal' => 6400000,
                ]);
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'raw_material_id' => 3, // Kain Sutra
                    'quantity' => 50,
                    'price' => 75000,
                    'subtotal' => 3750000,
                ]);
            } elseif ($po->supplier_id == 2) { // Supplier aksesoris
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'raw_material_id' => 5, // Resleting Metal 50cm
                    'quantity' => 200,
                    'price' => 4200,
                    'subtotal' => 840000,
                ]);
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'raw_material_id' => 6, // Kancing Baju Plastik
                    'quantity' => 500,
                    'price' => 280,
                    'subtotal' => 140000,
                ]);
            } elseif ($po->supplier_id == 3) { // Supplier bahan pelengkap
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'raw_material_id' => 7, // Benang Jahit Hitam
                    'quantity' => 30,
                    'price' => 11500,
                    'subtotal' => 345000,
                ]);
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'raw_material_id' => 9, // Tali Serut
                    'quantity' => 80,
                    'price' => 2500,
                    'subtotal' => 200000,
                ]);
            }

            // Auto calculate total cost
            $totalCost = $po->items()->sum('subtotal');
            $po->update(['total_cost' => $totalCost]);
        }

        // 7. Create Sales Orders
        $salesOrders = [
            [
                'customer_name' => 'Toko Baju Sumber Rejeki',
                'order_date' => now()->subDays(12),
                'status' => 'completed',
                'notes' => 'Pesanan untuk toko baju di pusat kota',
            ],
            [
                'customer_name' => 'Distributor Fashion Jatim',
                'order_date' => now()->subDays(6),
                'status' => 'processing',
                'notes' => 'Pesanan grosir untuk distributor regional',
            ],
            [
                'customer_name' => 'CV Busana Mandiri',
                'order_date' => now()->subDays(2),
                'status' => 'pending',
                'notes' => 'Pesanan seragam perusahaan',
            ]
        ];

        foreach ($salesOrders as $soData) {
            $so = SalesOrder::create($soData);

            // Add items to Sales Order
            if ($so->id == 1) {
                SalesOrderItem::create([
                    'sales_order_id' => $so->id,
                    'finished_good_id' => 1, // Baju Koko Pria
                    'quantity' => 8,
                    'price' => 85000,
                    'subtotal' => 680000,
                ]);
                SalesOrderItem::create([
                    'sales_order_id' => $so->id,
                    'finished_good_id' => 3, // Kaos Oblong
                    'quantity' => 15,
                    'price' => 45000,
                    'subtotal' => 675000,
                ]);
            } elseif ($so->id == 2) {
                SalesOrderItem::create([
                    'sales_order_id' => $so->id,
                    'finished_good_id' => 2, // Kebaya Modern
                    'quantity' => 5,
                    'price' => 185000,
                    'subtotal' => 925000,
                ]);
                SalesOrderItem::create([
                    'sales_order_id' => $so->id,
                    'finished_good_id' => 6, // Blus Wanita
                    'quantity' => 12,
                    'price' => 95000,
                    'subtotal' => 1140000,
                ]);
            } else {
                SalesOrderItem::create([
                    'sales_order_id' => $so->id,
                    'finished_good_id' => 7, // Jas Almamater
                    'quantity' => 25,
                    'price' => 75000,
                    'subtotal' => 1875000,
                ]);
                SalesOrderItem::create([
                    'sales_order_id' => $so->id,
                    'finished_good_id' => 8, // Seragam Sekolah
                    'quantity' => 20,
                    'price' => 110000,
                    'subtotal' => 2200000,
                ]);
            }

            // Auto calculate total amount
            $totalAmount = $so->items()->sum('subtotal');
            $so->update(['total_amount' => $totalAmount]);
        }

        // 8. Create Production Orders
        $productionOrders = [
            [
                'finished_good_id' => 1, // Baju Koko Pria
                'sales_order_id' => 3,
                'quantity' => 15,
                'start_date' => now()->subDays(4),
                'status' => 'in_progress',
                'notes' => 'Produksi baju koko untuk pesanan seragam',
            ],
            [
                'finished_good_id' => 3, // Kaos Oblong
                'sales_order_id' => null,
                'quantity' => 40,
                'start_date' => now()->subDays(1),
                'status' => 'pending',
                'notes' => 'Produksi kaos untuk stock toko',
            ],
            [
                'finished_good_id' => 8, // Seragam Sekolah
                'sales_order_id' => 3,
                'quantity' => 25,
                'start_date' => now(),
                'status' => 'completed',
                'notes' => 'Produksi seragam untuk pesanan sekolah',
            ]
        ];

        foreach ($productionOrders as $index => $prodData) {
            $prodData['production_code'] = 'PROD-' . now()->format('Ymd') . '-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
            $productionOrder = ProductionOrder::create($prodData);

            // Add production items based on recipe
            $product = $productionOrder->finishedGood;
            if ($product && $product->rawMaterials) {
                foreach ($product->rawMaterials as $material) {
                    $quantityUsed = $material->pivot->quantity * $productionOrder->quantity;
                    ProductionOrderItem::create([
                        'production_order_id' => $productionOrder->id,
                        'raw_material_id' => $material->id,
                        'quantity_used' => $quantityUsed,
                    ]);
                }
            }
        }

        $this->command->info('✅ Seeder Garment berhasil dijalankan!');
        $this->command->info('📊 Ringkasan Data:');
        $this->command->info('   - Admin: 1 (admin@gmail.com)');
        $this->command->info('   - Supplier: 3');
        $this->command->info('   - Bahan Baku: 9 jenis');
        $this->command->info('   - Produk Jadi: 8 jenis');
        $this->command->info('   - Purchase Order: 3');
        $this->command->info('   - Sales Order: 3');
        $this->command->info('   - Production Order: 3');
        $this->command->info('👤 Login: admin@gmail.com / password');
        $this->command->info('🎯 Sistem siap digunakan!');
    }
}
