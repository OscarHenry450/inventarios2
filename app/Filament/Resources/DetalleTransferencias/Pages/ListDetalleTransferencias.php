<?php

namespace App\Filament\Resources\DetalleTransferencias\Pages;

use App\Filament\Resources\DetalleTransferencias\DetalleTransferenciaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDetalleTransferencias extends ListRecords
{
    protected static string $resource = DetalleTransferenciaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
