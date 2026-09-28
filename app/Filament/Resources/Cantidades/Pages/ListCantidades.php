<?php

namespace App\Filament\Resources\Cantidades\Pages;

use App\Filament\Resources\Cantidades\CantidadeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCantidades extends ListRecords
{
    protected static string $resource = CantidadeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
