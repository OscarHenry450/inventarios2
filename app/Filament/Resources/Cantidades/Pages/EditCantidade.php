<?php

namespace App\Filament\Resources\Cantidades\Pages;

use App\Filament\Resources\Cantidades\CantidadeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCantidade extends EditRecord
{
    protected static string $resource = CantidadeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
