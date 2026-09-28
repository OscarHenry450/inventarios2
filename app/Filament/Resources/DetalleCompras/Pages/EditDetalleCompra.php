<?php

namespace App\Filament\Resources\DetalleCompras\Pages;

use App\Filament\Resources\DetalleCompras\DetalleCompraResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditDetalleCompra extends EditRecord
{
    protected static string $resource = DetalleCompraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
