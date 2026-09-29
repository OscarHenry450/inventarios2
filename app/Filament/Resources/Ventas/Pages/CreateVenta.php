<?php

namespace App\Filament\Resources\Ventas\Pages;

use App\Filament\Resources\Ventas\VentaResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateVenta extends CreateRecord
{
    protected static string $resource = VentaResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        if (! $user) {
            abort(403);
        }

        /*
         * No confiamos en user_id enviado
         * desde el navegador.
         */

        $data['user_id'] = auth()->id();

        $data['estado'] = 'pendiente';

        $data['fecha'] =
            $data['fecha'] ?? now();

        /*
         * Comprobar que puede usar esta ubicación.
         */
        if (! $user->hasRole('super_admin')) {
            $permitida = $user
                ->ubicaciones()
                ->whereKey($data['ubicacione_id'])
                ->where('tipo', 'tienda')
                ->exists();

            if (! $permitida) {
                throw ValidationException::withMessages([
                    'ubicacione_id' =>
                        'No tienes permiso para realizar ventas en esta tienda.',
                ]);
            }
        }

        return $data;
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
