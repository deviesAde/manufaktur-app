<?php

namespace App\Filament\Resources\ProductionOrderResource\RelationManagers;

use App\Models\RawMaterial;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $title = 'Raw Materials Required';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('raw_material_id')
                    ->label('Raw Material')
                    ->options(RawMaterial::query()->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, $get) {
                        if (!$state) {
                            return;
                        }

                        $material = RawMaterial::find($state);
                        if (!$material) {
                            return;
                        }

                        $productionQuantity = $get('../../quantity') ?? 1;
                        $finishedGood = $this->ownerRecord->finishedGood;

                        if ($finishedGood) {
                            $recipeItem = $finishedGood->rawMaterials()
                                ->where('raw_material_id', $state)
                                ->first();

                            if ($recipeItem) {
                                $requiredQuantity = $recipeItem->pivot->quantity * $productionQuantity;
                                $set('quantity_used', $requiredQuantity);
                            }
                        }
                    })
                    ->disabled(fn () => !$this->ownerRecord->can_edit),

                Forms\Components\TextInput::make('quantity_used')
                    ->label('Quantity Required')
                    ->numeric()
                    ->required()
                    ->minValue(0.01)
                    ->step(0.01)
                    ->disabled(fn () => !$this->ownerRecord->can_edit)
                    ->suffix(function ($get) {
                        $material = RawMaterial::find($get('raw_material_id'));
                        return $material?->unit ?? '';
                    }),

                Forms\Components\Placeholder::make('current_stock')
                    ->label('Current Stock')
                    ->content(function ($get) {
                        $materialId = $get('raw_material_id');
                        if (!$materialId) {
                            return 'N/A';
                        }

                        $material = RawMaterial::find($materialId);
                        return $material
                            ? number_format($material->stock) . ' ' . $material->unit
                            : 'N/A';
                    })
                    ->hidden(fn ($get) => !$get('raw_material_id')),

                Forms\Components\Placeholder::make('stock_status')
                    ->label('Stock Status')
                    ->content(function ($get) {
                        $materialId = $get('raw_material_id');
                        $quantityUsed = $get('quantity_used') ?? 0;

                        if (!$materialId || $quantityUsed <= 0) {
                            return 'N/A';
                        }

                        $material = RawMaterial::find($materialId);
                        if (!$material) {
                            return 'N/A';
                        }

                        return ($material->stock >= $quantityUsed)
                            ? '✅ Sufficient'
                            : '❌ Insufficient (Need: ' . number_format($quantityUsed - $material->stock) . ' more)';
                    })
                    ->hidden(fn ($get) => !$get('raw_material_id')),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->description('Raw materials needed for this production order')
            ->columns([
                Tables\Columns\TextColumn::make('rawMaterial.name')
                    ->label('Raw Material')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('quantity_used')
                    ->label('Quantity Required')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(function ($state, $record) {
                        return number_format($state, 2) . ' ' . $record->rawMaterial->unit;
                    }),

                Tables\Columns\TextColumn::make('rawMaterial.stock')
                    ->label('Current Stock')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(function ($state, $record) {
                        return number_format($state) . ' ' . $record->rawMaterial->unit;
                    }),

                Tables\Columns\IconColumn::make('is_stock_sufficient')
                    ->label('Stock Status')
                    ->getStateUsing(fn ($record) => $record->rawMaterial->stock >= $record->quantity_used)
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('stock_difference')
                    ->label('Shortage/Surplus')
                    ->getStateUsing(fn ($record) => $record->rawMaterial->stock - $record->quantity_used)
                    ->formatStateUsing(function ($state, $record) {
                        $abs = abs($state);

                        return $state >= 0
                            ? '+ ' . number_format($abs) . ' ' . $record->rawMaterial->unit
                            : '- ' . number_format($abs) . ' ' . $record->rawMaterial->unit;
                    })
                    ->color(fn ($state) => $state >= 0 ? 'success' : 'danger')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Raw Material')
                    ->visible(fn () => $this->ownerRecord->can_edit)
                    ->mutateFormDataUsing(function (array $data) {
                        $productionQuantity = $this->ownerRecord->quantity;
                        $finishedGood = $this->ownerRecord->finishedGood;

                        if ($finishedGood && isset($data['raw_material_id'])) {
                            $recipeItem = $finishedGood->rawMaterials()
                                ->where('raw_material_id', $data['raw_material_id'])
                                ->first();

                            if ($recipeItem && !isset($data['quantity_used'])) {
                                $data['quantity_used'] = $recipeItem->pivot->quantity * $productionQuantity;
                            }
                        }

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn () => $this->ownerRecord->can_edit),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => $this->ownerRecord->can_edit),

                Tables\Actions\Action::make('view_material')
                    ->label('View Material')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => route('filament.admin.resources.raw-materials.edit', $record->raw_material_id)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => $this->ownerRecord->can_edit),
                ]),
            ])
            ->emptyStateHeading('No raw materials added')
            ->emptyStateDescription('Add the raw materials required for this production order.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add First Material')
                    ->visible(fn () => $this->ownerRecord->can_edit),
            ]);
    }

    public function isReadOnly(): bool
    {
        return !$this->ownerRecord->can_edit;
    }
}
