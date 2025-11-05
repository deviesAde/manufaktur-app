<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Faker\Factory as Faker;

class GarmentSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

       

        // Suppliers
        $supplierIds = [];
        for ($i = 0; $i < 5; $i++) {
            $supplierIds[] = DB::table('suppliers')->insertGetId([
                'name' => $faker->company,
                'contact' => $faker->phoneNumber,
                'address' => $faker->address,
                'created_at' => Carbon::now()->subDays(rand(1, 60)),
                'updated_at' => Carbon::now()->subDays(rand(1, 30)),
            ]);
        }

        // Raw materials
        $rawIds = [];
        $rawList = ['Kain Katun','Benang Polyster','Kancing','Resleting','Papan Label','Garis Cetak','Karet','Perekat'];
        foreach ($rawList as $name) {
            $rawIds[] = DB::table('raw_materials')->insertGetId([
                'name' => $name,
                'unit' => 'pcs',
                'stock' => rand(50, 500),
                'min_stock' => rand(10, 50),
                'created_at' => Carbon::now()->subDays(rand(1, 90)),
                'updated_at' => Carbon::now()->subDays(rand(1, 30)),
            ]);
        }

        // Finished goods
        $fgIds = [];
        $fgList = ['Kaos Polos','Kemeja Kerja','Celana Panjang','Jaket Hoodie','Topi Baseball','Tas Selempang'];
        foreach ($fgList as $name) {
            $fgIds[] = DB::table('finished_goods')->insertGetId([
                'name' => $name,
                'unit' => 'pcs',
                'stock' => rand(10, 200),
                'price' => rand(50000, 300000),
                'created_at' => Carbon::now()->subDays(rand(1, 90)),
                'updated_at' => Carbon::now()->subDays(rand(1, 30)),
            ]);
        }

        // Finished good -> raw material mapping
        foreach ($fgIds as $fgId) {
            $needed = $faker->randomElements($rawIds, rand(2, 4));
            foreach ($needed as $rawId) {
                DB::table('finished_good_raw_material')->insert([
                    'finished_good_id' => $fgId,
                    'raw_material_id' => $rawId,
                    'quantity' => $faker->randomFloat(2, 0.1, 10),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }

        // Purchase orders + items
        $poIds = [];
        for ($p = 0; $p < 5; $p++) {
            $supplierId = $faker->randomElement($supplierIds);
            $orderDate = Carbon::now()->subDays(rand(1, 120));
            $poId = DB::table('purchase_orders')->insertGetId([
                'po_number' => 'PO'.Str::upper(Str::random(7)),
                'supplier_id' => $supplierId,
                'order_date' => $orderDate->toDateString(),
                'status' => $faker->randomElement(['Menunggu','Dikirim','Diterima']),
                'total_cost' => 0,
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ]);

            $total = 0;
            $itemsCount = rand(2, 4);
            $choices = $faker->randomElements($rawIds, $itemsCount);
            foreach ($choices as $rawId) {
                $qty = rand(10, 200);
                $price = rand(1000, 50000);
                $subtotal = $qty * $price;
                DB::table('purchase_order_items')->insert([
                    'purchase_order_id' => $poId,
                    'raw_material_id' => $rawId,
                    'quantity' => $qty,
                    'price' => $price,
                    'subtotal' => $subtotal,
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                ]);
                $total += $subtotal;
            }

            DB::table('purchase_orders')->where('id', $poId)->update(['total_cost' => $total]);
            $poIds[] = $poId;
        }

        // Sales orders + items
        $soIds = [];
        for ($s = 0; $s < 7; $s++) {
            $orderDate = Carbon::now()->subDays(rand(1, 100));
            $soId = DB::table('sales_orders')->insertGetId([
                'customer_name' => $faker->name,
                'order_date' => $orderDate->toDateString(),
                'status' => $faker->randomElement(['Dikirim','Diterima']),
                'total_amount' => 0,
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ]);

            $total = 0;
            $itemsCount = rand(1, 3);
            $choices = $faker->randomElements($fgIds, $itemsCount);
            foreach ($choices as $fgId) {
                $qty = rand(1, 10);
                $price = DB::table('finished_goods')->where('id', $fgId)->value('price') ?: rand(50000, 150000);
                $subtotal = $qty * $price;
                DB::table('sales_order_items')->insert([
                    'sales_order_id' => $soId,
                    'finished_good_id' => $fgId,
                    'quantity' => $qty,
                    'price' => $price,
                    'subtotal' => $subtotal,
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                ]);
                $total += $subtotal;
            }

            DB::table('sales_orders')->where('id', $soId)->update(['total_amount' => $total]);
            $soIds[] = $soId;
        }

        // Production orders, items and results
        for ($i = 0; $i < 6; $i++) {
            $linkedSo = $faker->optional(0.6)->randomElement($soIds);
            $start = Carbon::now()->subDays(rand(1, 40));
            $end = (rand(0,1) ? $start->copy()->addDays(rand(1,7)) : null);
            $poCode = 'PRD'.Str::upper(Str::random(6));
            $prodId = DB::table('production_orders')->insertGetId([
                'production_code' => $poCode,
                'sales_order_id' => $linkedSo,
                'start_date' => $start->toDateString(),
                'end_date' => $end ? $end->toDateString() : null,
                'status' => $faker->randomElement(['Pending','Proses','Selesai']),
                'notes' => $faker->optional()->sentence,
                'created_at' => $start,
                'updated_at' => $end ?: $start,
            ]);

            // items: use random raw materials
            $used = $faker->randomElements($rawIds, rand(1,3));
            foreach ($used as $rawId) {
                DB::table('production_order_items')->insert([
                    'production_order_id' => $prodId,
                    'raw_material_id' => $rawId,
                    'quantity_used' => rand(1, 100),
                    'created_at' => $start,
                    'updated_at' => $start,
                ]);
            }

            // production results: produce 1 or 2 finished goods
            $produced = $faker->randomElements($fgIds, rand(1,2));
            foreach ($produced as $fgId) {
                DB::table('production_results')->insert([
                    'production_order_id' => $prodId,
                    'finished_good_id' => $fgId,
                    'quantity' => rand(1, 50),
                    'created_at' => $start,
                    'updated_at' => $start,
                ]);
            }
        }
    }
}
