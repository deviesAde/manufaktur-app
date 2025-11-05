<?php

namespace App\Filament\Widgets;

use App\Models\RawMaterial;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class StockAlertWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
          ->query(
    RawMaterial::query()->where('stock', '<=', DB::raw('min_stock + 5'))
)
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Bahan Baku')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock')
                    ->label('Stok Tersedia')
                    ->color('danger')
                    ->sortable(),

                Tables\Columns\TextColumn::make('min_stock')
                    ->label('Stok Minimal')
                    ->color('gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('unit')
                    ->label('Satuan'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($record) => $record->stock <= $record->min_stock ? 'danger' : 'success')
                    ->formatStateUsing(fn ($record) => $record->stock <= $record->min_stock ? 'KRITIS' : 'AMAN'),
            ])
            ->heading('Peringatan Stok Rendah')
            ->description('Bahan baku yang perlu segera di-restock');
    }
}
