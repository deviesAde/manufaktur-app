<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RawMaterialResource\Pages;
use App\Filament\Resources\RawMaterialResource\RelationManagers;
use App\Models\RawMaterial;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RawMaterialResource extends Resource
{
    protected static ?string $model = RawMaterial::class;

    protected static ?string $navigationGroup = 'Inventory Management';
    protected static ?string $navigationIcon = 'heroicon-s-cube';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Material Name')
                    ->placeholder('Enter material name'),

                Forms\Components\TextInput::make('unit')
                    ->required()
                    ->maxLength(20)
                    ->default('pcs')
                    ->label('Unit of Measurement')
                    ->placeholder('e.g., kg, pcs, liter'),

                Forms\Components\TextInput::make('stock')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->label('Current Stock')
                    ->minValue(0),

                Forms\Components\TextInput::make('min_stock')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->label('Minimum Stock Level')
                    ->minValue(0)
                    ->helperText('Alert when stock falls below this level'),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Material Name'),

                Tables\Columns\TextColumn::make('unit')
                    ->searchable()
                    ->sortable()
                    ->label('Unit'),

                Tables\Columns\TextColumn::make('stock')
                    ->numeric()
                    ->sortable()
                    ->label('Current Stock')
                    ->color(fn ($record) => $record->stock <= $record->min_stock ? 'danger' : 'success')
                    ->weight(fn ($record) => $record->stock <= $record->min_stock ? 'bold' : 'normal'),

                Tables\Columns\TextColumn::make('min_stock')
                    ->numeric()
                    ->sortable()
                    ->label('Min Stock')
                    ->color('gray'),


                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Filter berdasarkan status stok
                Tables\Filters\SelectFilter::make('stock_status')
                    ->label('Stock Status')
                    ->options([
                        'low' => 'Low Stock',
                        'warning' => 'Warning',
                        'good' => 'Good',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            return match ($data['value']) {
                                'low' => $query->whereColumn('stock', '<=', 'min_stock'),
                                'warning' => $query->where('stock', '>', 'min_stock')
                                                   ->where('stock', '<=', \DB::raw('min_stock * 1.5')),
                                'good' => $query->where('stock', '>', \DB::raw('min_stock * 1.5')),
                            };
                        }
                        return $query;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Tambahkan relations jika diperlukan nanti
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRawMaterials::route('/'),
            'create' => Pages\CreateRawMaterial::route('/create'),
            'edit' => Pages\EditRawMaterial::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'unit'];
    }
}
