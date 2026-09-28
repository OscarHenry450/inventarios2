<?php

namespace App\Services;

use App\Models\Compra;
use App\Models\Inventario;
use Exception;
use Illuminate\Support\Facades\DB;

class CompraService
{
    public function confirmar(Compra $compra): void
    {
        DB::transaction(function () use ($compra) {

            // Volvemos a consultar y bloqueamos la compra
            $compra = Compra::query()
                ->with('detalles')
                ->lockForUpdate()
                ->findOrFail($compra->id);

            if ($compra->estado !== 'pendiente') {
                throw new Exception(
                    'La compra ya fue procesada o no se encuentra pendiente.'
                );
            }

            if ($compra->detalles->isEmpty()) {
                throw new Exception(
                    'No se puede confirmar una compra sin productos.'
                );
            }

            foreach ($compra->detalles as $detalle) {

                $inventario = Inventario::query()
                    ->where('producto_id', $detalle->producto_id)
                    ->where('ubicacione_id', $compra->ubicacione_id)
                    ->lockForUpdate()
                    ->first();

                if (! $inventario) {
                    $inventario = Inventario::create([
                        'producto_id' => $detalle->producto_id,
                        'ubicacione_id' => $compra->ubicacione_id,
                        'stock' => 0,
                        'stock_reservado' => 0,
                        'stock_minimo' => 0,
                    ]);
                }

                $inventario->increment(
                    'stock',
                    $detalle->cantidad
                );
            }

            $compra->update([
                'estado' => 'confirmada',
            ]);
        });
    }
    public function cancelar(Compra $compra): void
    {
        DB::transaction(function () use ($compra) {

            $compra = Compra::query()
                ->lockForUpdate()
                ->findOrFail($compra->id);

            if ($compra->estado !== 'pendiente') {
                throw new Exception(
                    'Solo se pueden cancelar compras pendientes.'
                );
            }

            $compra->update([
                'estado' => 'cancelada',
            ]);
        });
    }
}
