<?php

namespace App\Filament\Resources\DetalleTransferencias\Pages;

use App\Filament\Resources\DetalleTransferencias\DetalleTransferenciaResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewDetalleTransferencia extends ViewRecord
{
    protected static string $resource = DetalleTransferenciaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
