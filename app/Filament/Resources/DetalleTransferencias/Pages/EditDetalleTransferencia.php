<?php

namespace App\Filament\Resources\DetalleTransferencias\Pages;

use App\Filament\Resources\DetalleTransferencias\DetalleTransferenciaResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditDetalleTransferencia extends EditRecord
{
    protected static string $resource = DetalleTransferenciaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
