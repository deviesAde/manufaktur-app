<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductionOrderResource\Pages;
use App\Filament\Resources\ProductionOrderResource\RelationManagers;
use App\Models\ProductionOrder;
use App\Models\FinishedGood;
use App\Models\SalesOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductionOrderResource extends Resource
{
    protected static ?string $model = ProductionOrder::class;

    protected static ?string $navigationGroup = 'Production Management';
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Production Orders';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Production Information')
                ->schema([
                    Forms\Components\TextInput::make('production_code')
                        ->label('Production Code')
                        ->disabled()
                        ->dehydrated()

                        ->placeholder('AUTO-GENERATED'),

                    Forms\Components\Select::make('finished_good_id')
                        ->label('Finished Good')
                        ->relationship('finishedGood', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if ($state) {
                                $product = FinishedGood::find($state);
                                if ($product) {
                                    // Auto quantity based on min stock
                                    $set('quantity', max(10, $product->min_stock * 2));
                                }
                            }
                        }),

                    Forms\Components\Select::make('sales_order_id')
                        ->label('Sales Order (Optional)')
                        ->relationship('salesOrder', 'customer_name')
                        ->searchable()
                        ->preload()
                        ->nullable(),

                    Forms\Components\TextInput::make('quantity')
                        ->label('Production Quantity')
                        ->numeric()
                        ->required()
                        ->minValue(1)
                        ->default(10),

                    Forms\Components\DatePicker::make('start_date')
                        ->label('Start Date')
                        ->required()
                        ->default(now()),

                    Forms\Components\DatePicker::make('end_date')
                        ->label('Expected Completion')
                        ->nullable()
                        ->minDate(fn ($get) => $get('start_date')),

                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options([
                            'pending' => 'Pending',
                            'in_progress' => 'In Progress',
                            'completed' => 'Completed',
                            'cancelled' => 'Cancelled',
                        ])
                        ->default('pending')
                        ->required()
                        ->reactive(),

                    Forms\Components\Textarea::make('notes')
                        ->label('Notes')
                        ->nullable()
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('production_code')
                    ->label('Production Code')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Production code copied!'),

                Tables\Columns\TextColumn::make('finishedGood.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('finishedGood.brand')
                    ->label('Brand')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'batik_jogja' => 'info',
                        'fashion_solo' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match($state) {
                        'batik_jogja' => 'Batik Jogja',
                        'fashion_solo' => 'Fashion Solo',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Quantity')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state)),

                Tables\Columns\TextColumn::make('salesOrder.customer_name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Start Date')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('Expected Date')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'gray' => 'pending',
                        'warning' => 'in_progress',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'pending' => 'Pending',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Materials')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->label('Status Filter'),

                Tables\Filters\SelectFilter::make('finished_good')
                    ->relationship('finishedGood', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Product Filter'),

                Tables\Filters\SelectFilter::make('brand')
                    ->options([
                        'batik_jogja' => 'Batik Jogja',
                        'fashion_solo' => 'Fashion Solo',
                    ])
                    ->label('Brand Filter')
                    ->query(function ($query, $data) {
                        if (!empty($data['value'])) {
                            $query->whereHas('finishedGood', function ($q) use ($data) {
                                $q->where('brand', $data['value']);
                            });
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn (ProductionOrder $record) => in_array($record->status, ['pending', 'in_progress'])),
                Tables\Actions\Action::make('startProduction')
                    ->label('Start Production')
                    ->color('warning')
                    ->icon('heroicon-o-play')
                    ->action(function (ProductionOrder $record) {
                        $record->update(['status' => 'in_progress']);
                    })
                    ->visible(fn (ProductionOrder $record) => $record->status === 'pending'),
                Tables\Actions\Action::make('completeProduction')
                    ->label('Complete')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->action(function (ProductionOrder $record) {
                        // Simple completion tanpa validasi bahan baku dulu
                        $record->update([
                            'status' => 'completed',
                            'end_date' => now()
                        ]);
                    })
                    ->visible(fn (ProductionOrder $record) => $record->status === 'in_progress'),
                Tables\Actions\Action::make('cancelProduction')
                    ->label('Cancel')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->action(function (ProductionOrder $record) {
                        $record->update(['status' => 'cancelled']);
                    })
                    ->visible(fn (ProductionOrder $record) => in_array($record->status, ['pending', 'in_progress'])),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (ProductionOrder $record) => $record->status === 'pending'),
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
            'index' => Pages\ListProductionOrders::route('/'),
            'create' => Pages\CreateProductionOrder::route('/create'),
            'edit' => Pages\EditProductionOrder::route('/{record}/edit'),
            'view' => Pages\ViewProductionOrder::route('/{record}'),
        ];
    }
}
