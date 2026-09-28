<?php

namespace App\Filament\Resources\DetalleCompras\Pages;

use App\Filament\Resources\DetalleCompras\DetalleCompraResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewDetalleCompra extends ViewRecord
{
    protected static string $resource = DetalleCompraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
