<?php

namespace App\Filament\Resources\Transferencias\Pages;

use App\Filament\Resources\Transferencias\TransferenciaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTransferencia extends CreateRecord
{
    protected static string $resource = TransferenciaResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        $data['estado'] = 'pendiente';

        return $data;
    }
}
