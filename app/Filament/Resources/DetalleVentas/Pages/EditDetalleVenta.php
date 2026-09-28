<?php

namespace App\Filament\Resources\DetalleVentas\Pages;

use App\Filament\Resources\DetalleVentas\DetalleVentaResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditDetalleVenta extends EditRecord
{
    protected static string $resource = DetalleVentaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
