<?php

namespace App\Filament\Resources\ProductionOrderResource\RelationManagers;

use App\Models\RawMaterial;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ProductionOrderItemRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $recordTitleAttribute = 'raw_material_id';

    // Remove 'static' keyword - make it an instance method
    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('raw_material_id')
                ->label('Bahan Baku')
                ->relationship('rawMaterial', 'name')
                ->required(),

            Forms\Components\TextInput::make('quantity_used')
                ->label('Jumlah Digunakan')
                ->numeric()
                ->required(),
        ]);
    }

    // Remove 'static' keyword - make it an instance method
    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('rawMaterial.name')->label('Bahan Baku'),
                Tables\Columns\TextColumn::make('quantity_used')->label('Jumlah Digunakan'),
                Tables\Columns\TextColumn::make('created_at')->label('Dibuat')->dateTime()->toggleable(),
            ])
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
