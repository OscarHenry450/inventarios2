<?php

namespace App\Filament\Resources\DetalleVentas\Pages;

use App\Filament\Resources\DetalleVentas\DetalleVentaResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewDetalleVenta extends ViewRecord
{
    protected static string $resource = DetalleVentaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
