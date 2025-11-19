<?php

namespace App\Filament\Resources\SalesOrderResource\RelationManagers;

use App\Models\FinishedGood;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Order Items';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('finished_good_id')
                    ->label('Product')
                    ->options(FinishedGood::all()->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $product = FinishedGood::find($state);
                            if ($product) {
                                $set('price', $product->price);
                                // Tampilkan info stok
                                $set('stock_info', "Available: {$product->stock}");
                            }
                        }
                    })
                    ->disabled(fn () => !$this->ownerRecord->can_edit),

                Forms\Components\TextInput::make('quantity')
                    ->label('Quantity')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->default(1)
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                        $this->calculateSubtotal($get, $set);
                    })
                    ->disabled(fn () => !$this->ownerRecord->can_edit),

                Forms\Components\TextInput::make('price')
                    ->label('Unit Price')
                    ->numeric()
                    ->required()
                    ->prefix('Rp')
                    ->minValue(0)
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                        $this->calculateSubtotal($get, $set);
                    })
                    ->disabled(fn () => !$this->ownerRecord->can_edit),

                Forms\Components\TextInput::make('subtotal')
                    ->label('Subtotal')
                    ->numeric()
                    ->disabled()
                    ->prefix('Rp')
                    ->default(0),

                Forms\Components\Placeholder::make('stock_info')
                    ->label('Stock Information')
                    ->content(function ($get) {
                        $productId = $get('finished_good_id');
                        if (!$productId) return 'Select a product';

                        $product = FinishedGood::find($productId);
                        $quantity = (float) ($get('quantity') ?? 0);

                        if (!$product) return 'Product not found';

                        $isSufficient = $product->stock >= $quantity;
                        $status = $isSufficient ? '✅ Sufficient' : '❌ Insufficient';

                        return "Available: {$product->stock} | Status: {$status}";
                    })
                    ->hidden(fn ($get) => !$get('finished_good_id')),
            ]);
    }

    private function calculateSubtotal(callable $get, callable $set)
    {
        $quantity = (float) ($get('quantity') ?? 0);
        $price = (float) ($get('price') ?? 0);
        $set('subtotal', $quantity * $price);
    }

    public function table(Table $table): Table
    {
        return $table
            ->description('Products ordered by customer')
            ->columns([
                Tables\Columns\TextColumn::make('finishedGood.name')
                    ->label('Product')
                    ->sortable()
                    ->searchable(),
                    // HAPUS URL YANG ERROR

                Tables\Columns\TextColumn::make('finishedGood.brand')
                    ->label('Brand')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'brand_a' => 'info',
                        'brand_b' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Quantity')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state)),

                Tables\Columns\TextColumn::make('price')
                    ->label('Unit Price')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('IDR')
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\IconColumn::make('is_stock_sufficient')
                    ->label('Stock Check')
                    ->getStateUsing(fn ($record) => $record->finishedGood->stock >= $record->quantity)
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                Tables\Columns\TextColumn::make('finishedGood.stock')
                    ->label('Available Stock')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state, $record) => number_format($state)),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Product')
                    ->visible(fn () => $this->ownerRecord->can_edit)
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['subtotal'] = ((float) ($data['quantity'] ?? 0)) * ((float) ($data['price'] ?? 0));
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn () => $this->ownerRecord->can_edit)
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['subtotal'] = ((float) ($data['quantity'] ?? 0)) * ((float) ($data['price'] ?? 0));
                        return $data;
                    }),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => $this->ownerRecord->can_edit),
                // HAPUS ACTION view_product YANG ERROR
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => $this->ownerRecord->can_edit),
                ]),
            ])
            ->emptyStateHeading('No products added')
            ->emptyStateDescription('Add products to this sales order.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add First Product')
                    ->visible(fn () => $this->ownerRecord->can_edit),
            ]);
    }

    public function isReadOnly(): bool
    {
        return !$this->ownerRecord->can_edit;
    }
}
