<?php

namespace App\Filament\Widgets;

use App\Models\RawMaterial;
use App\Models\SalesOrder;
use App\Models\FinishedGood;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class QuickStatsWidget extends Widget
{
    protected static string $view = 'filament.widgets.quick-stats-widget';

    protected int | string | array $columnSpan = 'full';

    public function getViewData(): array
    {
        return [
            'safeStock' => RawMaterial::where('stock', '>', DB::raw('min_stock * 1.5'))->count(),
           'lowStock' => RawMaterial::whereRaw('stock <= min_stock + 5')->count(),
            'todaySales' => SalesOrder::whereDate('created_at', today())->count(),
            'totalProducts' => FinishedGood::count(),
        ];
    }
}
