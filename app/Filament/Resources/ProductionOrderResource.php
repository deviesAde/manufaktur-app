<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductionOrderResource\Pages;
use App\Filament\Resources\ProductionOrderResource\RelationManagers\ProductionOrderItemRelationManager;
use App\Models\ProductionOrder;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;

class ProductionOrderResource extends Resource
{
    protected static ?string $model = ProductionOrder::class;

    protected static ?string $navigationGroup = 'Manajemen Produksi';
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Order Produksi';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('production_code')
                ->label('Kode Produksi')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),

            Forms\Components\Select::make('sales_order_id')
                ->label('Customer')
                ->relationship('salesOrder', 'customer_name')
                ->searchable()
                ->preload(),

            Forms\Components\DatePicker::make('start_date')
                ->label('Tanggal Mulai')
                ->required(),

            Forms\Components\DatePicker::make('end_date')
                ->label('Tanggal Selesai'),

            Forms\Components\Select::make('status')
                ->label('Status Produksi')
                ->options([
                    'Pending' => 'Pending',
                    'Proses' => 'Proses',
                    'Selesai' => 'Selesai',
                ])
                ->default('Pending')
                ->required(),

            Forms\Components\Textarea::make('notes')
                ->label('Catatan')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('production_code')->label('Kode Produksi')->searchable(),
            Tables\Columns\TextColumn::make('salesOrder.customer_name')->label('Customer')->sortable(),
            Tables\Columns\TextColumn::make('start_date')->label('Mulai')->date(),
            Tables\Columns\TextColumn::make('end_date')->label('Selesai')->date(),
            Tables\Columns\BadgeColumn::make('status')
                ->label('Status')
                ->colors([
                    'secondary' => 'Pending',
                    'warning' => 'Proses',
                    'success' => 'Selesai',
                ])
                ->sortable(),
            Tables\Columns\TextColumn::make('created_at')->label('Dibuat')->dateTime()->toggleable(isToggledHiddenByDefault: true),
        ])
        ->defaultSort('created_at', 'desc')
        ->actions([
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            ProductionOrderItemRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductionOrders::route('/'),
            'create' => Pages\CreateProductionOrder::route('/create'),
            'edit' => Pages\EditProductionOrder::route('/{record}/edit'),
        ];
    }
}
