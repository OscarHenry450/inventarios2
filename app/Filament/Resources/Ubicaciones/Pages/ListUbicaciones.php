<?php

namespace App\Filament\Resources\Ubicaciones\Pages;

use App\Filament\Resources\Ubicaciones\UbicacioneResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUbicaciones extends ListRecords
{
    protected static string $resource = UbicacioneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
