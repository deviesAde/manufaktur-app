<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesOrderResource\Pages;
use App\Filament\Resources\SalesOrderResource\RelationManagers;
use App\Models\SalesOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SalesOrderResource extends Resource
{
    protected static ?string $model = SalesOrder::class;

    protected static ?string $navigationGroup = 'Sales & Distribution';
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'Sales Orders';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Order Information')
                ->schema([
                    // HAPUS: brand field
                    Forms\Components\TextInput::make('customer_name')
                        ->label('Customer Name')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Enter customer name'),

                    Forms\Components\DatePicker::make('order_date')
                        ->label('Order Date')
                        ->required()
                        ->default(now()),

                    Forms\Components\Select::make('status')
                        ->label('Order Status')
                        ->options([
                            'pending' => 'Pending',
                            'processing' => 'Processing',
                            'completed' => 'Completed',
                            'cancelled' => 'Cancelled',
                        ])
                        ->default('pending')
                        ->required()
                        ->reactive(),

                    Forms\Components\TextInput::make('total_amount')
                        ->label('Total Amount')
                        ->numeric()
                        ->prefix('Rp')
                        ->placeholder('Otomatis terhitung berdasarkan items')
                        ->disabled()
                        ->dehydrated(),

                    Forms\Components\Textarea::make('notes')
                        ->label('Notes')
                        ->nullable()
                        ->placeholder('Additional order notes...')
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                // HAPUS: brand column

                Tables\Columns\TextColumn::make('order_date')
                    ->label('Order Date')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'gray' => 'pending',
                        'blue' => 'processing',
                        'green' => 'completed',
                        'red' => 'cancelled',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->money('IDR')
                    ->sortable()
                    ->color('success')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Items')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('production_orders_count')
                    ->counts('productionOrders')
                    ->label('Production Orders')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('order_date', 'desc')
            ->filters([
                // HAPUS: brand filter

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->label('Status Filter'),

                Tables\Filters\Filter::make('order_date')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('From Date'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Until Date'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn ($query, $date) => $query->whereDate('order_date', '>=', $date)
                            )
                            ->when(
                                $data['created_until'],
                                fn ($query, $date) => $query->whereDate('order_date', '<=', $date)
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn (SalesOrder $record) => $record->can_edit),
                Tables\Actions\Action::make('processOrder')
                    ->label('Process Order')
                    ->color('blue')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->action(function (SalesOrder $record) {
                        if (!$record->canProcess()) {
                            throw new \Exception("Insufficient stock to process this order");
                        }
                        $record->update(['status' => 'processing']);
                    })
                    ->visible(fn (SalesOrder $record) => $record->status === 'pending'),
                Tables\Actions\Action::make('completeOrder')
                    ->label('Complete Order')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->action(function (SalesOrder $record) {
                        $record->update(['status' => 'completed']);
                    })
                    ->visible(fn (SalesOrder $record) => $record->status === 'processing'),
                Tables\Actions\Action::make('cancelOrder')
                    ->label('Cancel')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->action(function (SalesOrder $record) {
                        $record->update(['status' => 'cancelled']);
                    })
                    ->visible(fn (SalesOrder $record) => in_array($record->status, ['pending', 'processing'])),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (SalesOrder $record) => $record->status === 'pending'),
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
            RelationManagers\ItemsRelationManager::class,
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
