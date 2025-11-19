<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FinishedGoodResource\Pages;
use App\Filament\Resources\FinishedGoodResource\RelationManagers;
use App\Models\FinishedGood;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FinishedGoodResource extends Resource
{
    protected static ?string $model = FinishedGood::class;

    protected static ?string $navigationGroup = 'Inventory Management';
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Product Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Product Name'),

                        Forms\Components\Select::make('unit')
                            ->required()
                            ->options([
                                'pcs' => 'Pieces',
                                'set' => 'Set',
                                'pack' => 'Pack',
                                'box' => 'Box',
                            ])
                            ->default('pcs')
                            ->label('Unit'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Stock & Pricing')
                    ->schema([
                        Forms\Components\TextInput::make('stock')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->label('Current Stock'),

                        Forms\Components\TextInput::make('min_stock')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(10)
                            ->label('Minimum Stock'),

                        Forms\Components\TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp')
                            ->label('Selling Price'),

                        Forms\Components\TextInput::make('production_cost')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp')
                            ->label('Production Cost'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->nullable()
                            ->label('Description')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Product Name')
                    ->weight('medium')
                    ->description(fn ($record) => $record->description ? \Str::limit($record->description, 50) : 'No description')
                    ->tooltip(fn ($record) => $record->description),

                Tables\Columns\TextColumn::make('unit')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pcs' => 'info',
                        'set' => 'warning',
                        'pack' => 'success',
                        'box' => 'danger',
                        default => 'gray',
                    })
                    ->sortable()
                    ->label('Unit'),

                Tables\Columns\TextColumn::make('stock')
                    ->numeric()
                    ->sortable()
                    ->label('Stock')
                    ->color(fn ($record) => $record->needsProduction() ? 'danger' : 'success')
                    ->weight(fn ($record) => $record->needsProduction() ? 'bold' : 'normal')
                    ->formatStateUsing(fn ($state) => number_format($state)),

                Tables\Columns\TextColumn::make('min_stock')
                    ->numeric()
                    ->sortable()
                    ->label('Min Stock')
                    ->color('gray')
                    ->formatStateUsing(fn ($state) => number_format($state)),

                Tables\Columns\TextColumn::make('price')
                    ->money('IDR')
                    ->sortable()
                    ->label('Price')
                    ->color('success'),

                Tables\Columns\TextColumn::make('production_cost')
                    ->money('IDR')
                    ->sortable()
                    ->label('Production Cost')
                    ->color('warning'),

                Tables\Columns\TextColumn::make('profit_margin')
                    ->label('Profit Margin')
                    ->getStateUsing(fn ($record) => $record->price - $record->production_cost)
                    ->money('IDR')
                    ->color('green')
                    ->sortable(),

                Tables\Columns\IconColumn::make('needs_production')
                    ->label('Need Production')
                    ->getStateUsing(fn ($record) => $record->needsProduction())
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Created'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('unit')
                    ->options([
                        'pcs' => 'Pieces',
                        'set' => 'Set',
                        'pack' => 'Pack',
                        'box' => 'Box',
                    ])
                    ->label('Filter by Unit'),

                Tables\Filters\Filter::make('needs_production')
                    ->label('Need Production')
                    ->query(fn (Builder $query): Builder => $query->whereRaw('stock <= min_stock')),

                Tables\Filters\Filter::make('low_stock')
                    ->label('Low Stock (< 20% min stock)')
                    ->query(fn (Builder $query): Builder => $query->whereRaw('stock < min_stock * 0.2')),

                Tables\Filters\Filter::make('good_stock')
                    ->label('Good Stock (> min stock)')
                    ->query(fn (Builder $query): Builder => $query->whereRaw('stock > min_stock')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('quick_production')
                    ->label('Quick Production')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->color('warning')
                    ->url(fn ($record) => \App\Filament\Resources\ProductionOrderResource::getUrl('create', ['finished_good_id' => $record->id]))
                    ->visible(fn ($record) => $record->needsProduction()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->defaultSort('stock', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\RawMaterialsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFinishedGoods::route('/'),
            'create' => Pages\CreateFinishedGood::route('/create'),
            'edit' => Pages\EditFinishedGood::route('/{record}/edit'),
            
        ];
    }
}
