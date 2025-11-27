<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseOrderResource\Pages;
use App\Filament\Resources\PurchaseOrderResource\RelationManagers;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Procurement';
    protected static ?string $navigationLabel = 'Purchase Orders';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Purchase Order Information')
                ->schema([
                    Forms\Components\TextInput::make('po_number')
                        ->label('PO Number')
                        ->disabled()
                        ->dehydrated()
                        ->helperText('Will be auto-generated upon save'),

                    Forms\Components\Select::make('supplier_id')
                        ->label('Supplier')
                        ->relationship('supplier', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('contact_person')
                                ->maxLength(255),
                            Forms\Components\TextInput::make('phone')
                                ->tel(),
                        ]),

                    Forms\Components\DatePicker::make('order_date')
                        ->label('Order Date')
                        ->required()
                        ->default(now()),

                    Forms\Components\DatePicker::make('expected_date')
                        ->label('Expected Delivery')
                        ->nullable(),

                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options([
                            'Menunggu' => 'Menunggu',
                            'Dikirim' => 'Dikirim',
                            'Diterima' => 'Diterima',
                            'Dibatalkan' => 'Dibatalkan',
                        ])
                        ->default('Menunggu')
                        ->required(),

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
                Tables\Columns\TextColumn::make('po_number')
                    ->label('PO Number')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('order_date')
                    ->label('Order Date')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('expected_date')
                    ->label('Expected Date')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'gray' => 'Menunggu',
                        'warning' => 'Dikirim',
                        'success' => 'Diterima',
                        'danger' => 'Dibatalkan',
                    ]),

                Tables\Columns\TextColumn::make('total_cost')
                    ->label('Total Cost')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Items')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y, H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('order_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Menunggu' => 'Menunggu',
                        'Dikirim' => 'Dikirim',
                        'Diterima' => 'Diterima',
                        'Dibatalkan' => 'Dibatalkan',
                    ])
                    ->label('Status Filter'),

                Tables\Filters\SelectFilter::make('supplier')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Supplier Filter'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn (PurchaseOrder $record) => $record->can_edit),
                Tables\Actions\Action::make('markReceived')
                    ->label('Mark Received')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->action(function (PurchaseOrder $record) {
                        $record->update(['status' => 'Diterima']);
                    })
                    ->visible(fn (PurchaseOrder $record) => $record->status === 'Dikirim'),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (PurchaseOrder $record) => $record->can_cancel),
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
            RelationManagers\ItemsRelationManager::class, // TAMBAH INI
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchaseOrders::route('/'),
            'create' => Pages\CreatePurchaseOrder::route('/create'),
            'edit' => Pages\EditPurchaseOrder::route('/{record}/edit'),

        ];
    }
}
