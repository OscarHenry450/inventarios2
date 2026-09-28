<?php
namespace App\Services;

use App\Models\Inventario;
use App\Models\Transferencia;
use Exception;
use Illuminate\Support\Facades\DB;

class TransferenciaService
{
    public function preparar(Transferencia $transferencia): void
    {
        DB::transaction(function () use ($transferencia) {

            $transferencia = Transferencia::query()
                ->with('detalles')
                ->lockForUpdate()
                ->findOrFail($transferencia->id);

            if ($transferencia->estado !== 'pendiente') {
                throw new Exception(
                    'Solo se pueden preparar transferencias pendientes.'
                );
            }

            if ($transferencia->detalles->isEmpty()) {
                throw new Exception(
                    'La transferencia no tiene productos.'
                );
            }

            if (
                $transferencia->ubicacion_origen_id
                === $transferencia->ubicacion_destino_id
            ) {
                throw new Exception(
                    'La ubicación de origen y destino no pueden ser iguales.'
                );
            }

            /*
             * Primero validamos TODO.
             */
            foreach ($transferencia->detalles as $detalle) {

                $inventario = Inventario::query()
                    ->where(
                        'producto_id',
                        $detalle->producto_id
                    )
                    ->where(
                        'ubicacione_id',
                        $transferencia->ubicacion_origen_id
                    )
                    ->lockForUpdate()
                    ->first();

                if (! $inventario) {
                    throw new Exception(
                        "El producto {$detalle->producto->nombre} no tiene inventario en la ubicación de origen."
                    );
                }

                $stockDisponible =
                    (float) $inventario->stock
                    - (float) $inventario->stock_reservado;

                if (
                    $stockDisponible
                    < (float) $detalle->cantidad_solicitada
                ) {
                    throw new Exception(
                        "Stock insuficiente para {$detalle->producto->nombre}. Disponible: {$stockDisponible}."
                    );
                }
            }

            /*
             * Si todo está correcto,
             * reservamos el stock.
             */
            foreach ($transferencia->detalles as $detalle) {

                $inventario = Inventario::query()
                    ->where(
                        'producto_id',
                        $detalle->producto_id
                    )
                    ->where(
                        'ubicacione_id',
                        $transferencia->ubicacion_origen_id
                    )
                    ->lockForUpdate()
                    ->firstOrFail();

                $inventario->increment(
                    'stock_reservado',
                    $detalle->cantidad_solicitada
                );
            }

            $transferencia->update([
                'estado' => 'preparando',
            ]);
        });
    }

    public function enviar(Transferencia $transferencia): void
    {
        DB::transaction(function () use ($transferencia) {

            $transferencia = Transferencia::query()
                ->with('detalles')
                ->lockForUpdate()
                ->findOrFail($transferencia->id);

            if ($transferencia->estado !== 'preparando') {
                throw new Exception(
                    'Solo se pueden enviar transferencias que están en preparación.'
                );
            }

            foreach ($transferencia->detalles as $detalle) {

                $inventario = Inventario::query()
                    ->where(
                        'producto_id',
                        $detalle->producto_id
                    )
                    ->where(
                        'ubicacione_id',
                        $transferencia->ubicacion_origen_id
                    )
                    ->lockForUpdate()
                    ->firstOrFail();

                $cantidad = (float) $detalle->cantidad_solicitada;

                if ((float) $inventario->stock < $cantidad) {
                    throw new Exception(
                        "Stock físico insuficiente para {$detalle->producto->nombre}."
                    );
                }

                if (
                    (float) $inventario->stock_reservado
                    < $cantidad
                ) {
                    throw new Exception(
                        "La reserva de stock de {$detalle->producto->nombre} es inconsistente."
                    );
                }

                $inventario->decrement(
                    'stock',
                    $cantidad
                );

                $inventario->decrement(
                    'stock_reservado',
                    $cantidad
                );

                $detalle->update([
                    'cantidad_enviada' => $cantidad,
                ]);
            }

            $transferencia->update([
                'estado' => 'en_transito',
                'fecha_envio' => now(),
            ]);
        });
    }

    public function completar(
        Transferencia $transferencia
    ): void {
        DB::transaction(function () use ($transferencia) {

            $transferencia = Transferencia::query()
                ->with('detalles')
                ->lockForUpdate()
                ->findOrFail($transferencia->id);

            if ($transferencia->estado !== 'en_transito') {
                throw new Exception(
                    'Solo se pueden recibir transferencias que estén en tránsito.'
                );
            }

            foreach ($transferencia->detalles as $detalle) {

                $cantidad = (float) $detalle->cantidad_enviada;

                if ($cantidad <= 0) {
                    throw new Exception(
                        "El producto {$detalle->producto->nombre} no tiene una cantidad enviada válida."
                    );
                }

                $inventarioDestino = Inventario::query()
                    ->where(
                        'producto_id',
                        $detalle->producto_id
                    )
                    ->where(
                        'ubicacione_id',
                        $transferencia->ubicacion_destino_id
                    )
                    ->lockForUpdate()
                    ->first();

                if (! $inventarioDestino) {
                    $inventarioDestino = Inventario::create([
                        'producto_id' => $detalle->producto_id,
                        'ubicacione_id' =>
                            $transferencia->ubicacion_destino_id,
                        'stock' => 0,
                        'stock_reservado' => 0,
                        'stock_minimo' => 0,
                    ]);
                }

                $inventarioDestino->increment(
                    'stock',
                    $cantidad
                );

                $detalle->update([
                    'cantidad_recibida' => $cantidad,
                ]);
            }

            $transferencia->update([
                'estado' => 'completada',
                'fecha_recepcion' => now(),
                'usuario_recibe_id' => auth()->id(),
            ]);
        });
    }

    public function cancelar(
        Transferencia $transferencia
    ): void {
        DB::transaction(function () use ($transferencia) {

            $transferencia = Transferencia::query()
                ->with('detalles')
                ->lockForUpdate()
                ->findOrFail($transferencia->id);

            if ($transferencia->estado === 'pendiente') {

                $transferencia->update([
                    'estado' => 'cancelada',
                ]);

                return;
            }

            if ($transferencia->estado === 'preparando') {

                foreach ($transferencia->detalles as $detalle) {

                    $inventario = Inventario::query()
                        ->where(
                            'producto_id',
                            $detalle->producto_id
                        )
                        ->where(
                            'ubicacione_id',
                            $transferencia->ubicacion_origen_id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();

                    $cantidad =
                        (float) $detalle->cantidad_solicitada;

                    if (
                        (float) $inventario->stock_reservado
                        < $cantidad
                    ) {
                        throw new Exception(
                            "La reserva del producto {$detalle->producto->nombre} es inconsistente."
                        );
                    }

                    $inventario->decrement(
                        'stock_reservado',
                        $cantidad
                    );
                }

                $transferencia->update([
                    'estado' => 'cancelada',
                ]);

                return;
            }

            throw new Exception(
                'No se puede cancelar una transferencia que ya fue enviada o completada.'
            );
        });
    }
}
