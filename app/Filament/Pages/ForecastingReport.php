<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\SalesOrderItem;
use App\Models\FinishedGood;
use App\Models\RawMaterial;
use App\Models\PurchaseOrder;
use App\Models\ProductionOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ForecastingReport extends Page
{
    protected static ?string $navigationLabel = 'Forecasting & Laporan';
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $slug = 'forecasting-report';

    protected static string $view = 'filament.pages.forecasting-report';

    public $forecastResults = [];
    public $purchaseSuggestions = [];
    public $summaryData = [];
    public $salesChartData = [];
    public $inventoryChartData = [];
    public $movementChartData = [];

    public function mount()
    {
        $this->calculateForecast();
        $this->prepareSummaryData();
        $this->prepareSalesChartData();
        $this->prepareInventoryChartData();
        $this->prepareMovementChartData();
    }

    public function calculateForecast()
    {
        // Ambil data historis penjualan 6 bulan terakhir
        $sixMonthsAgo = Carbon::now()->subMonths(6);

        $salesData = DB::table('sales_order_items')
            ->join('finished_goods', 'sales_order_items.finished_good_id', '=', 'finished_goods.id')
            ->where('sales_order_items.created_at', '>=', $sixMonthsAgo)
            ->select(
                'sales_order_items.finished_good_id',
                'finished_goods.name as product_name',
                'sales_order_items.quantity',
                'sales_order_items.created_at'
            )
            ->get();

        $groupedItems = $salesData->groupBy('finished_good_id');
        $forecastResults = [];
        $purchaseSuggestions = [];

        foreach ($groupedItems as $finishedGoodId => $orders) {
            if ($orders->isEmpty()) continue;

            $productName = $orders->first()->product_name;

            // ✅ METODE MOVING AVERAGE 3 BULAN
            $monthlyData = $this->getMonthlySalesData($finishedGoodId, 6);
            $last3Months = array_slice($monthlyData, -3);
            $movingAverage = array_sum($last3Months) / count($last3Months);
            $nextMonthForecast = round($movingAverage);

            $forecastResults[] = [
                'product' => $productName,
                'last_3_months' => array_sum($last3Months),
                'moving_average' => round($movingAverage, 1),
                'next_month_forecast' => $nextMonthForecast,
                'trend' => $this->calculateSalesTrend($monthlyData),
            ];

            // ✅ JIT MURNI: HANYA HITUNG PEMBELIAN UNTUK BULAN DEPAN
            $finishedGood = FinishedGood::with('rawMaterials')->find($finishedGoodId);

            if ($finishedGood && $finishedGood->rawMaterials->isNotEmpty()) {
                foreach ($finishedGood->rawMaterials as $rawMaterial) {
                    // JIT: Hanya hitung kebutuhan bulan depan
                    $requiredQty = $rawMaterial->pivot->quantity * $nextMonthForecast;

                    // JIT: Langsung kebutuhan - stok saat ini (TANPA SAFETY STOCK)
                    $shortage = $requiredQty - $rawMaterial->stock;

                    // JIT: Hanya beli jika benar-benar butuh
                    if ($shortage > 0) {
                        $suggestedPurchase = ceil($shortage);

                        $purchaseSuggestions[] = [
                            'product' => $productName,
                            'raw_material' => $rawMaterial->name,
                            'required_qty' => round($requiredQty),
                            'current_stock' => $rawMaterial->stock,
                            'suggested_purchase' => $suggestedPurchase,
                            'unit' => $rawMaterial->unit,
                            'priority' => $this->calculateJITPriority($rawMaterial->stock, $requiredQty),
                        ];
                    }
                }
            }
        }

        $this->forecastResults = $forecastResults;
        $this->purchaseSuggestions = $purchaseSuggestions;
    }

   
    private function calculateJITPriority($currentStock, $requiredQty)
    {
        if ($currentStock <= 0) {
            return 'URGENT - Stock Out';
        } elseif ($currentStock < ($requiredQty * 0.5)) {
            return 'HIGH - Critical Low';
        } elseif ($currentStock < $requiredQty) {
            return 'MEDIUM - Need Soon';
        } else {
            return 'LOW - No Purchase';
        }
    }
    // TAMBAHKAN METHOD UNTUK HITUNG PRIORITAS
    private function calculatePriority($currentStock, $minStock, $requiredQty, $totalNeeded)
    {
        if ($currentStock < $minStock) {
            return 'Kritis';
        } elseif ($currentStock < $requiredQty) {
            return 'Tinggi';
        } elseif ($currentStock < $totalNeeded) {
            return 'Sedang';
        } else {
            return 'Rendah';
        }
    }

    public function prepareSummaryData()
    {
        // Data Ringkas Stok
        $totalRawMaterials = RawMaterial::count();
        $lowStockMaterials = RawMaterial::where('stock', '<', DB::raw('min_stock'))->count();
        $outOfStockMaterials = RawMaterial::where('stock', '<=', 0)->count();

        // Data Ringkas Pembelian
        $totalPurchaseOrders = PurchaseOrder::count();
        $pendingPurchases = PurchaseOrder::where('status', 'pending')->count();
        $thisMonthPurchases = PurchaseOrder::whereMonth('created_at', Carbon::now()->month)->count();

        // Data Ringkas Produksi
        $totalProductionOrders = ProductionOrder::count();
        $activeProductions = ProductionOrder::where('status', 'in_progress')->count();
        $completedThisMonth = ProductionOrder::where('status', 'completed')
            ->whereMonth('created_at', Carbon::now()->month)
            ->count();

        // Data Ringkas Penjualan
        $totalSales = SalesOrderItem::sum('quantity');
        $thisMonthSales = SalesOrderItem::whereMonth('created_at', Carbon::now()->month)->sum('quantity');
        $lastMonthSales = SalesOrderItem::whereMonth('created_at', Carbon::now()->subMonth()->month)->sum('quantity');
        $salesGrowth = $lastMonthSales > 0 ?
            round((($thisMonthSales - $lastMonthSales) / $lastMonthSales) * 100, 1) : 0;

        $this->summaryData = [
            'stok' => [
                'total_bahan' => $totalRawMaterials,
                'stok_rendah' => $lowStockMaterials,
                'stok_habis' => $outOfStockMaterials,
            ],
            'pembelian' => [
                'total_po' => $totalPurchaseOrders,
                'pending' => $pendingPurchases,
                'bulan_ini' => $thisMonthPurchases,
            ],
            'produksi' => [
                'total_produksi' => $totalProductionOrders,
                'sedang_proses' => $activeProductions,
                'selesai_bulan_ini' => $completedThisMonth,
            ],
            'penjualan' => [
                'total_penjualan' => $totalSales,
                'bulan_ini' => $thisMonthSales,
                'pertumbuhan' => $salesGrowth,
            ]
        ];
    }

    public function prepareSalesChartData()
    {
        $last6Months = collect([]);
        $monthlySales = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $last6Months[] = $month->format('M Y');

            $sales = SalesOrderItem::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('quantity');
            $monthlySales[] = $sales;
        }

        $this->salesChartData = [
            'labels' => $last6Months->toArray(),
            'datasets' => [
                [
                    'label' => 'Penjualan per Bulan',
                    'data' => $monthlySales,
                    'borderColor' => '#4f46e5',
                    'backgroundColor' => 'rgba(79, 70, 229, 0.1)',
                    'borderWidth' => 3,
                    'fill' => true,
                    'tension' => 0.4,
                ]
            ],
        ];
    }

    public function prepareInventoryChartData()
    {
        $materials = RawMaterial::orderBy('stock', 'asc')->limit(10)->get();

        $labels = $materials->pluck('name')->toArray();
        $stockData = $materials->pluck('stock')->toArray();
        $minStockData = $materials->pluck('min_stock')->toArray();

        $this->inventoryChartData = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Stok Saat Ini',
                    'data' => $stockData,
                    'backgroundColor' => '#10b981',
                    'borderColor' => '#10b981',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Stok Minimum',
                    'data' => $minStockData,
                    'backgroundColor' => '#ef4444',
                    'borderColor' => '#ef4444',
                    'borderWidth' => 2,
                    'borderDash' => [5, 5],
                ]
            ],
        ];
    }

    public function prepareMovementChartData()
    {
        $last6Months = collect([]);
        $salesData = [];
        $purchaseData = [];
        $productionData = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $last6Months[] = $month->format('M Y');

            // Penjualan
            $salesData[] = SalesOrderItem::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('quantity');

            // Pembelian (asumsi ada model PurchaseOrderItem)
            $purchaseData[] = DB::table('purchase_order_items')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('quantity');

            // Produksi
            $productionData[] = ProductionOrder::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        }

        $this->movementChartData = [
            'labels' => $last6Months->toArray(),
            'datasets' => [
                [
                    'label' => 'Penjualan',
                    'data' => $salesData,
                    'borderColor' => '#4f46e5',
                    'backgroundColor' => 'rgba(79, 70, 229, 0.1)',
                    'borderWidth' => 2,
                    'fill' => false,
                ],
                [
                    'label' => 'Pembelian',
                    'data' => $purchaseData,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'borderWidth' => 2,
                    'fill' => false,
                ],
                [
                    'label' => 'Produksi',
                    'data' => $productionData,
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'borderWidth' => 2,
                    'fill' => false,
                ]
            ],
        ];
    }

    private function getMonthlySalesData($productId, $months = 6)
    {
        $data = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $startDate = Carbon::now()->subMonths($i)->startOfMonth();
            $endDate = Carbon::now()->subMonths($i)->endOfMonth();

            $qty = DB::table('sales_order_items')
                ->where('finished_good_id', $productId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('quantity');

            $data[] = $qty;
        }
        return $data;
    }

    private function calculateSalesTrend($data)
    {
        if (count($data) < 2) return 'stabil';

        $recent = array_slice($data, -3);
        $previous = array_slice($data, -6, 3);

        $recentAvg = array_sum($recent) / count($recent);
        $previousAvg = array_sum($previous) / count($previous);

        if ($recentAvg > $previousAvg * 1.1) return 'naik';
        if ($recentAvg < $previousAvg * 0.9) return 'turun';
        return 'stabil';
    }
}
