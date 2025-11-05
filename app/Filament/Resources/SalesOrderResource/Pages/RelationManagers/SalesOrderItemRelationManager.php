<?php

namespace App\Filament\Resources\SalesOrderResource\RelationManagers;

use App\Models\FinishedGood;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\RelationManagers\RelationManager;

class SalesOrderItemRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $title = 'Items Pesanan';
    protected static ?string $recordTitleAttribute = 'finished_good_id';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('finished_good_id')
                ->label('Produk Jadi')
                ->options(FinishedGood::all()->pluck('name', 'id'))
                ->searchable()
                ->required()
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set) {
                    if ($state) {
                        $product = FinishedGood::find($state);
                        if ($product) {
                            $set('price', $product->price);
                            $this->calculateSubtotal($set, $get ?? null);
                        }
                    }
                }),

            Forms\Components\TextInput::make('quantity')
                ->label('Jumlah')
                ->numeric()
                ->minValue(1)
                ->required()
                ->reactive()
                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                    $this->calculateSubtotal($set, $get);
                }),

            Forms\Components\TextInput::make('price')
                ->label('Harga')
                ->numeric()
                ->required()
                ->reactive()
                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                    $this->calculateSubtotal($set, $get);
                }),

            Forms\Components\TextInput::make('subtotal')
                ->label('Subtotal')
                ->numeric()
                ->disabled()
                ->default(0),
        ]);
    }

    private function calculateSubtotal(callable $set, ?callable $get = null)
    {
        if ($get) {
            $quantity = $get('quantity') ?? 0;
            $price = $get('price') ?? 0;
            $subtotal = $quantity * $price;
            $set('subtotal', $subtotal);
        }
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('finishedGood.name')
                    ->label('Produk')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Jumlah')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state)),

                Tables\Columns\TextColumn::make('price')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        // Hitung subtotal sebelum save
                        $data['subtotal'] = ($data['quantity'] ?? 0) * ($data['price'] ?? 0);
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        // Hitung subtotal sebelum update
                        $data['subtotal'] = ($data['quantity'] ?? 0) * ($data['price'] ?? 0);
                        return $data;
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
