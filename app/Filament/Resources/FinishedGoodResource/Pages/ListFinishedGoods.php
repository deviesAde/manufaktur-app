<?php

namespace App\Filament\Resources\FinishedGoodResource\Pages;

use App\Filament\Resources\FinishedGoodResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFinishedGoods extends ListRecords
{
    protected static string $resource = FinishedGoodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
