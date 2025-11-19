<?php

namespace App\Filament\Resources\FinishedGoodResource\Pages;

use App\Filament\Resources\FinishedGoodResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateFinishedGood extends CreateRecord
{
    protected static string $resource = FinishedGoodResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Finished Good created successfully';
    }
}
