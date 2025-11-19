<?php

namespace App\Filament\Resources\FinishedGoodResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\RelationManagers\RelationManager;

class RawMaterialsRelationManager extends RelationManager
{
    protected static string $relationship = 'rawMaterials';

    protected static ?string $title = 'Production Recipe';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->disabled()
                    ->label('Raw Material'),

                Forms\Components\TextInput::make('pivot.quantity')
                    ->numeric()
                    ->disabled()
                    ->label('Quantity Required'),

                Forms\Components\TextInput::make('unit')
                    ->disabled()
                    ->label('Unit'),
            ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Raw Material'),

                Tables\Columns\TextColumn::make('pivot.quantity')
                    ->label('Quantity Required'),

                Tables\Columns\TextColumn::make('unit')
                    ->label('Unit'),

                Tables\Columns\TextColumn::make('stock')
                    ->label('Current Stock')
                    ->color(fn ($record) => $record->stock < $record->pivot->quantity ? 'danger' : 'success'),
            ])
            ->headerActions([]) // NO CREATE
            ->actions([]) // NO EDIT/DELETE
            ->bulkActions([]); // NO BULK ACTIONS
    }
}
