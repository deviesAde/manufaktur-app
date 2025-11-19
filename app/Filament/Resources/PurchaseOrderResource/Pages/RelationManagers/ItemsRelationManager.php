<?php

namespace App\Filament\Resources\PurchaseOrderResource\RelationManagers;

use App\Models\RawMaterial;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Purchase Items';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('raw_material_id')
                    ->label('Raw Material')
                    ->options(RawMaterial::all()->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $material = RawMaterial::find($state);
                            if ($material) {
                                $set('price', 0); // Default price, bisa diisi manual
                            }
                        }
                    }),

                Forms\Components\TextInput::make('quantity')
                    ->label('Quantity')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                        $this->calculateSubtotal($get, $set);
                    }),

                Forms\Components\TextInput::make('price')
                    ->label('Unit Price')
                    ->numeric()
                    ->required()
                    ->prefix('Rp')
                    ->minValue(0)
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                        $this->calculateSubtotal($get, $set);
                    }),

                Forms\Components\TextInput::make('subtotal')
                    ->label('Subtotal')
                    ->numeric()
                    ->disabled()
                    ->prefix('Rp')
                    ->default(0),

                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->nullable()
                    ->columnSpanFull(),
            ]);
    }

    private function calculateSubtotal(callable $get, callable $set)
    {
        $quantity = $get('quantity') ?? 0;
        $price = $get('price') ?? 0;
        $set('subtotal', $quantity * $price);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('rawMaterial.name')
                    ->label('Raw Material')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Quantity')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state)),

                Tables\Columns\TextColumn::make('price')
                    ->label('Unit Price')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->visible(fn ($livewire) => $livewire->ownerRecord->can_edit),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn ($livewire) => $livewire->ownerRecord->can_edit),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($livewire) => $livewire->ownerRecord->can_edit),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn ($livewire) => $livewire->ownerRecord->can_edit),
                ]),
            ]);
    }
}
