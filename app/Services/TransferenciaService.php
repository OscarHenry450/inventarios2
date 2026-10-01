<?php
namespace App\Services;

use App\Models\Inventario;
use App\Models\Transferencia;
use App\Models\User;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class TransferenciaService
{
    public function enviar(Transferencia $transferencia): void
    {
        $destinatario = null;

        DB::transaction(function () use (
            $transferencia,
            &$destinatario
        ): void {

            $transferencia = Transferencia::query()
                ->with([
                    'detalles.producto',
                    'ubicacionOrigen',
                    'ubicacionDestino',
                ])
                ->lockForUpdate()
                ->findOrFail($transferencia->id);

            /*
             * 1. Debe estar pendiente.
             */
            if ($transferencia->estado !== 'pendiente') {
                throw new Exception(
                    'La transferencia ya fue procesada.'
                );
            }

            /*
             * 2. No debe haber sido enviada anteriormente.
             */
            if ($transferencia->fecha_envio !== null) {
                throw new Exception(
                    'Esta transferencia ya fue enviada.'
                );
            }

            /*
             * 3. Solamente el usuario que creó la
             * transferencia puede enviarla.
             */
            if (
                (int) $transferencia->user_id
                !== (int) auth()->id()
            ) {
                throw new Exception(
                    'No puedes enviar una transferencia creada por otro usuario.'
                );
            }

            /*
             * 4. Verificar que el usuario tenga asignado
             * el origen.
             */
            $tieneOrigen = auth()->user()
                ->ubicaciones()
                ->where(
                    'ubicaciones.id',
                    $transferencia->ubicacion_origen_id
                )
                ->exists();

            if (! $tieneOrigen) {
                throw new Exception(
                    'Ya no tienes acceso a la ubicación de origen.'
                );
            }

            /*
             * 5. Origen y destino no pueden ser iguales.
             */
            if (
                (int) $transferencia->ubicacion_origen_id
                === (int) $transferencia->ubicacion_destino_id
            ) {
                throw new Exception(
                    'La ubicación de origen y destino no pueden ser iguales.'
                );
            }

            /*
             * 6. Debe tener productos.
             */
            if ($transferencia->detalles->isEmpty()) {
                throw new Exception(
                    'Debes agregar al menos un producto antes de enviar la transferencia.'
                );
            }

            /*
             * 7. Comprobar que el destinatario todavía
             * pertenece a la ubicación destino.
             */
            $destinatario = User::query()
                ->whereKey(
                    $transferencia->usuario_recibe_id
                )
                ->whereHas(
                    'ubicaciones',
                    fn ($query) =>
                    $query->where(
                        'ubicaciones.id',
                        $transferencia->ubicacion_destino_id
                    )
                )
                ->first();

            if (! $destinatario) {
                throw new Exception(
                    'El usuario destinatario ya no pertenece a la ubicación de destino.'
                );
            }

            /*
             * 8. VALIDAR TODO EL INVENTARIO PRIMERO.
             *
             * No descontamos todavía.
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
                        "El producto {$detalle->producto->nombre} ya no existe en el inventario de origen."
                    );
                }

                $disponible =
                    (float) $inventario->stock
                    - (float) $inventario->stock_reservado;

                $solicitado =
                    (float) $detalle->cantidad_solicitada;

                if ($solicitado <= 0) {
                    throw new Exception(
                        "La cantidad de {$detalle->producto->nombre} no es válida."
                    );
                }

                if ($disponible < $solicitado) {
                    throw new Exception(
                        "Stock insuficiente para {$detalle->producto->nombre}. Disponible: {$disponible}; solicitado: {$solicitado}."
                    );
                }
            }

            /*
             * 9. Como TODO está correcto, descontamos
             * físicamente del origen.
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

                $cantidad =
                    (float) $detalle->cantidad_solicitada;

                $inventario->decrement(
                    'stock',
                    $cantidad
                );

                /*
                 * Dejamos una copia de lo realmente enviado.
                 */
                $detalle->update([
                    'cantidad_enviada' => $cantidad,
                ]);
            }

            /*
             * Sigue pendiente porque el destinatario
             * todavía no confirmó la recepción.
             */
            $transferencia->update([
                'fecha_envio' => now(),
            ]);
        });

        /*
         * Notificamos DESPUÉS de que la transacción
         * terminó correctamente.
         */
        if ($destinatario) {

            Notification::make()
                ->title('Nueva transferencia')
                ->body(
                    'Tienes una transferencia pendiente de recepción.'
                )
                ->info()
                ->sendToDatabase($destinatario);
        }
    }
    public function recibir(Transferencia $transferencia): void
    {
        DB::transaction(function () use ($transferencia): void {

            $transferencia = Transferencia::query()
                ->with([
                    'detalles.producto',
                    'ubicacionDestino',
                ])
                ->lockForUpdate()
                ->findOrFail($transferencia->id);

            /*
             * 1. Debe seguir pendiente.
             */
            if ($transferencia->estado !== 'pendiente') {
                throw new Exception(
                    'Esta transferencia ya fue procesada.'
                );
            }

            /*
             * 2. Tiene que haber sido enviada.
             */
            if ($transferencia->fecha_envio === null) {
                throw new Exception(
                    'Esta transferencia todavía no fue enviada.'
                );
            }

            /*
             * 3. SOLO el destinatario puede recibirla.
             */
            if (
                (int) $transferencia->usuario_recibe_id
                !== (int) auth()->id()
            ) {
                throw new Exception(
                    'No eres el destinatario de esta transferencia.'
                );
            }

            /*
             * 4. Verificar que el destinatario todavía
             * tenga acceso a la ubicación destino.
             */
            $tieneDestino = auth()->user()
                ->ubicaciones()
                ->where(
                    'ubicaciones.id',
                    $transferencia->ubicacion_destino_id
                )
                ->exists();

            if (! $tieneDestino) {
                throw new Exception(
                    'No tienes acceso a la ubicación de destino.'
                );
            }

            /*
             * 5. Debe tener productos.
             */
            if ($transferencia->detalles->isEmpty()) {
                throw new Exception(
                    'La transferencia no contiene productos.'
                );
            }

            /*
             * 6. Validar cantidades enviadas.
             */
            foreach ($transferencia->detalles as $detalle) {

                if (
                    $detalle->cantidad_enviada === null
                    || (float) $detalle->cantidad_enviada <= 0
                ) {
                    throw new Exception(
                        "El producto {$detalle->producto->nombre} no tiene una cantidad enviada válida."
                    );
                }
            }

            /*
             * 7. Agregar los productos al inventario
             * de la ubicación destino.
             */
            foreach ($transferencia->detalles as $detalle) {

                $cantidad =
                    (float) $detalle->cantidad_enviada;

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

                /*
                 * Si el producto todavía no existe
                 * en esa tienda/almacén, lo creamos.
                 */
                if (! $inventarioDestino) {

                    $inventarioDestino =
                        Inventario::create([
                            'producto_id' =>
                                $detalle->producto_id,

                            'ubicacione_id' =>
                                $transferencia
                                    ->ubicacion_destino_id,

                            'stock' => 0,

                            'stock_reservado' => 0,

                            'stock_minimo' => 0,
                        ]);
                }

                /*
                 * Ahora entra físicamente al destino.
                 */
                $inventarioDestino->increment(
                    'stock',
                    $cantidad
                );

                /*
                 * Guardamos cuánto fue recibido.
                 */
                $detalle->update([
                    'cantidad_recibida' => $cantidad,
                ]);
            }

            /*
             * 8. Completar transferencia.
             */
            $transferencia->update([
                'estado' => 'completada',
                'fecha_recepcion' => now(),
            ]);
        });
    }
}
