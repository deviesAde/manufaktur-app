<?php

namespace App\Filament\Resources\ProductionOrderResource\Pages;

use App\Filament\Resources\ProductionOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProductionOrder extends ViewRecord
{
    protected static string $resource = ProductionOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn () => in_array($this->record->status, ['pending', 'in_progress'])),

            Actions\Action::make('startProduction')
                ->label('Start Production')
                ->color('warning')
                ->icon('heroicon-o-play')
                ->action(function () {
                    $this->record->update(['status' => 'in_progress']);
                    $this->refreshFormData(['status']);
                })
                ->visible(fn () => $this->record->status === 'pending'),

            Actions\Action::make('completeProduction')
                ->label('Complete')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->action(function () {
                    $this->record->update([
                        'status' => 'completed',
                        'end_date' => now()
                    ]);
                    $this->refreshFormData(['status', 'end_date']);
                })
                ->visible(fn () => $this->record->status === 'in_progress'),

            Actions\Action::make('cancelProduction')
                ->label('Cancel')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->action(function () {
                    $this->record->update(['status' => 'cancelled']);
                    $this->refreshFormData(['status']);
                })
                ->visible(fn () => in_array($this->record->status, ['pending', 'in_progress'])),
        ];
    }

   
}
