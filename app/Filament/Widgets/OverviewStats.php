<?php

namespace App\Filament\Widgets;

use App\Models\RawMaterial;
use App\Models\SalesOrder;
use App\Models\ProductionOrder;
use App\Models\PurchaseOrder;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class OverviewStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Bahan Baku', RawMaterial::count())
                ->description('Jumlah material tersedia')
                ->descriptionIcon('heroicon-o-cube')
                ->color('success'),


            Stat::make('Penjualan Bulan Ini', SalesOrder::whereMonth('created_at', now()->month)->count())
                ->description(now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-o-shopping-cart')
                ->color('primary'),


            Stat::make('Produksi Aktif', ProductionOrder::where('status', 'Proses')->count())
                ->description('Sedang diproses')
                ->descriptionIcon('heroicon-o-cog')
                ->color('warning'),


            Stat::make('PO Pending', PurchaseOrder::where('status', 'Menunggu')->count())
                ->description('Menunggu konfirmasi')
                ->descriptionIcon('heroicon-o-clock')
                ->color('danger')

        ];
    }
}
