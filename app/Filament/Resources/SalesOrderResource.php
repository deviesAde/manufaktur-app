<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesOrderResource\Pages;
use App\Models\SalesOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\SalesOrderResource\RelationManagers\SalesOrderItemRelationManager;

class SalesOrderResource extends Resource
{
    protected static ?string $model = SalesOrder::class;
    protected static ?string $navigationGroup = 'Sales & Distribution';
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'Penjualan Produk Jadi';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('customer_name')
                ->label('Nama Customer')
                ->required(),

            Forms\Components\DatePicker::make('order_date')
                ->label('Tanggal Pesanan')
                ->default(now())
                ->required(),

            Forms\Components\Select::make('status')
                ->label('Status Pesanan')
                ->options([
                    'Pending' => 'Pending',
                    'Dikirim' => 'Dikirim',
                    'Diterima' => 'Diterima',
                ])
                ->default('Pending')
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable(),

                Tables\Columns\TextColumn::make('order_date')
                    ->label('Tanggal Pesanan')
                    ->date(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status Pesanan')
                    ->colors([
                        'secondary' => 'Pending',
                        'info' => 'Dikirim',
                        'success' => 'Diterima',
                    ]),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime(),
            ])
            ->filters([])
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
            SalesOrderItemRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalesOrders::route('/'),
            'create' => Pages\CreateSalesOrder::route('/create'),
            'edit' => Pages\EditSalesOrder::route('/{record}/edit'),
        ];
    }
}
