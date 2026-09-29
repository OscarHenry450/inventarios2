<?php

namespace App\Services;

use App\Models\Inventario;
use App\Models\ProductoPrecio;
use App\Models\Venta;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VentaService
{
    /**
     * Devuelve todos los precios que están permitidos
     * para determinada cantidad de unidades.
     */
    public function preciosPermitidos(
        int $productoId,
        float $cantidad
    ): Collection {
        return ProductoPrecio::query()
            ->where('producto_id', $productoId)
            ->where('activo', true)
            ->whereHas('cantidad', function ($query) use ($cantidad) {
                $query->where(
                    'cantidad_unidades',
                    '<=',
                    $cantidad
                );
            })
            ->with('cantidad')
            ->get()
            ->sortBy('cantidad.cantidad_unidades')
            ->values();
    }

    /**
     * Comprueba que el precio seleccionado realmente
     * esté permitido para la cantidad vendida.
     */
    public function validarPrecio(
        int $productoId,
        int $productoPrecioId,
        float $cantidad
    ): ProductoPrecio {
        $precio = ProductoPrecio::query()
            ->whereKey($productoPrecioId)
            ->where('producto_id', $productoId)
            ->where('activo', true)
            ->with('cantidad')
            ->first();

        if (! $precio) {
            throw ValidationException::withMessages([
                'producto_precio_id' =>
                    'El precio seleccionado no pertenece al producto.',
            ]);
        }

        if (
            (float) $cantidad
            < (float) $precio->cantidad->cantidad_unidades
        ) {
            throw ValidationException::withMessages([
                'producto_precio_id' =>
                    'La cantidad vendida no alcanza el mínimo requerido para este precio.',
            ]);
        }

        return $precio;
    }
    public function precioRecomendado(
        int $productoId,
        float $cantidad
    ): ?ProductoPrecio {
        return $this
            ->preciosPermitidos($productoId, $cantidad)
            ->sortByDesc(
                fn (ProductoPrecio $precio) =>
                $precio->cantidad->cantidad_unidades
            )
            ->first();
    }
    public function calcularSubtotal(
        float $cantidad,
        float $precioUnitario,
        float $descuento = 0
    ): float {
        $subtotal = ($cantidad * $precioUnitario) - $descuento;

        return max(
            round($subtotal, 2),
            0
        );
    }
    public function obtenerInventario(
        int $productoId,
        int $ubicacionId
    ): Inventario {
        $inventario = Inventario::query()
            ->where('producto_id', $productoId)
            ->where('ubicacione_id', $ubicacionId)
            ->first();

        if (! $inventario) {
            throw ValidationException::withMessages([
                'producto_id' =>
                    'Este producto no tiene inventario en la ubicación seleccionada.',
            ]);
        }

        return $inventario;
    }
    public function validarStockDisponible(
        int $productoId,
        int $ubicacionId,
        float $cantidad
    ): Inventario {
        $inventario = $this->obtenerInventario(
            $productoId,
            $ubicacionId
        );

        $disponible =
            (float) $inventario->stock
            - (float) $inventario->stock_reservado;

        if ($cantidad > $disponible) {
            throw ValidationException::withMessages([
                'cantidad' =>
                    "Stock insuficiente. Disponible: {$disponible} unidades.",
            ]);
        }

        return $inventario;
    }
    public static function reservar(Venta $venta): void
    {
        DB::transaction(function () use ($venta): void {

            /*
             * Volvemos a obtener la venta bloqueándola.
             */
            $venta = Venta::query()
                ->with('detalles.producto')
                ->lockForUpdate()
                ->findOrFail($venta->id);

            /*
             * Solo una venta pendiente puede reservarse.
             */
            if ($venta->estado !== 'pendiente') {
                throw ValidationException::withMessages([
                    'venta' => 'Solo una venta pendiente puede ser reservada.',
                ]);
            }

            if ($venta->detalles->isEmpty()) {
                throw ValidationException::withMessages([
                    'venta' => 'La venta no tiene productos.',
                ]);
            }

            /*
             * PRIMERA PASADA:
             * validamos TODO antes de modificar inventario.
             */
            foreach ($venta->detalles as $detalle) {

                $inventario = Inventario::query()
                    ->where(
                        'ubicacione_id',
                        $venta->ubicacione_id
                    )
                    ->where(
                        'producto_id',
                        $detalle->producto_id
                    )
                    ->lockForUpdate()
                    ->first();

                if (! $inventario) {
                    throw ValidationException::withMessages([
                        'inventario' =>
                            "El producto {$detalle->producto->nombre} no tiene inventario en esta ubicación.",
                    ]);
                }

                $stockDisponible =
                    (float) $inventario->stock
                    -
                    (float) $inventario->stock_reservado;

                if (
                    (float) $detalle->cantidad
                    > $stockDisponible
                ) {
                    throw ValidationException::withMessages([
                        'stock' =>
                            "Stock insuficiente para {$detalle->producto->nombre}. Disponible: {$stockDisponible}.",
                    ]);
                }
            }

            /*
             * SEGUNDA PASADA:
             * como todo está correcto, reservamos.
             */
            foreach ($venta->detalles as $detalle) {

                $inventario = Inventario::query()
                    ->where(
                        'ubicacione_id',
                        $venta->ubicacione_id
                    )
                    ->where(
                        'producto_id',
                        $detalle->producto_id
                    )
                    ->lockForUpdate()
                    ->firstOrFail();

                $inventario->increment(
                    'stock_reservado',
                    $detalle->cantidad
                );
            }

            /*
             * Finalmente cambiamos el estado.
             */
            $venta->update([
                'estado' => 'reservada',
                'fecha_reserva' => now(),
            ]);
        });
    }
    public static function vender(Venta $venta): void
    {
        DB::transaction(function () use ($venta): void {

            /*
             * Bloqueamos la venta para evitar que dos procesos
             * intenten confirmarla simultáneamente.
             */
            $venta = Venta::query()
                ->with('detalles.producto')
                ->lockForUpdate()
                ->findOrFail($venta->id);

            /*
             * Solo podemos vender una venta pendiente
             * o una venta reservada.
             */
            if (! in_array(
                $venta->estado,
                ['pendiente', 'reservada'],
                true
            )) {
                throw ValidationException::withMessages([
                    'venta' =>
                        'Solo una venta pendiente o reservada puede confirmarse.',
                ]);
            }

            if ($venta->detalles->isEmpty()) {
                throw ValidationException::withMessages([
                    'venta' =>
                        'La venta no tiene productos.',
                ]);
            }

            /*
             * ==========================================
             * PRIMERA PASADA
             * Validamos todos los productos.
             * ==========================================
             */
            foreach ($venta->detalles as $detalle) {

                $inventario = Inventario::query()
                    ->where(
                        'ubicacione_id',
                        $venta->ubicacione_id
                    )
                    ->where(
                        'producto_id',
                        $detalle->producto_id
                    )
                    ->lockForUpdate()
                    ->first();

                if (! $inventario) {

                    throw ValidationException::withMessages([
                        'inventario' =>
                            "El producto {$detalle->producto->nombre} no tiene inventario en esta ubicación.",
                    ]);
                }

                $cantidad =
                    (float) $detalle->cantidad;

                /*
                 * ======================================
                 * CASO 1:
                 * venta PENDIENTE
                 * ======================================
                 *
                 * Como todavía no reservamos nada,
                 * comprobamos:
                 *
                 * stock - stock_reservado
                 */
                if ($venta->estado === 'pendiente') {

                    $stockDisponible =
                        (float) $inventario->stock
                        -
                        (float) $inventario->stock_reservado;

                    if ($cantidad > $stockDisponible) {

                        throw ValidationException::withMessages([
                            'stock' =>
                                "Stock insuficiente para {$detalle->producto->nombre}. Disponible: {$stockDisponible}.",
                        ]);
                    }
                }

                /*
                 * ======================================
                 * CASO 2:
                 * venta RESERVADA
                 * ======================================
                 *
                 * La cantidad ya debería formar parte
                 * de stock_reservado.
                 */
                if ($venta->estado === 'reservada') {

                    if (
                        $cantidad >
                        (float) $inventario->stock_reservado
                    ) {

                        throw ValidationException::withMessages([
                            'stock' =>
                                "La reserva de {$detalle->producto->nombre} es inconsistente con el inventario.",
                        ]);
                    }

                    if (
                        $cantidad >
                        (float) $inventario->stock
                    ) {

                        throw ValidationException::withMessages([
                            'stock' =>
                                "No existe suficiente stock físico para {$detalle->producto->nombre}.",
                        ]);
                    }
                }
            }

            /*
             * ==========================================
             * SEGUNDA PASADA
             * Modificamos inventarios.
             * ==========================================
             */
            foreach ($venta->detalles as $detalle) {

                $inventario = Inventario::query()
                    ->where(
                        'ubicacione_id',
                        $venta->ubicacione_id
                    )
                    ->where(
                        'producto_id',
                        $detalle->producto_id
                    )
                    ->lockForUpdate()
                    ->firstOrFail();

                $cantidad =
                    (float) $detalle->cantidad;

                /*
                 * PENDIENTE → VENDIDA
                 *
                 * Nunca estuvo reservada.
                 */
                if ($venta->estado === 'pendiente') {

                    $inventario->decrement(
                        'stock',
                        $cantidad
                    );
                }

                /*
                 * RESERVADA → VENDIDA
                 *
                 * Sale físicamente del inventario
                 * y deja de estar reservada.
                 */
                if ($venta->estado === 'reservada') {

                    $inventario->decrement(
                        'stock',
                        $cantidad
                    );

                    $inventario->decrement(
                        'stock_reservado',
                        $cantidad
                    );
                }
            }

            /*
             * ==========================================
             * Finalmente confirmamos la venta.
             * ==========================================
             */
            $venta->update([
                'estado' => 'vendida',
                'fecha_venta' => now(),
            ]);
        });
    }
    public static function cancelar(Venta $venta): void
    {
        DB::transaction(function () use ($venta): void {

            /*
             * Bloqueamos la venta.
             */
            $venta = Venta::query()
                ->with('detalles.producto')
                ->lockForUpdate()
                ->findOrFail($venta->id);

            /*
             * Solo pendiente o reservada pueden cancelarse.
             */
            if (! in_array(
                $venta->estado,
                ['pendiente', 'reservada'],
                true
            )) {
                throw ValidationException::withMessages([
                    'venta' =>
                        'Solo una venta pendiente o reservada puede cancelarse.',
                ]);
            }

            /*
             * =========================================
             * CASO 1
             * PENDIENTE → CANCELADA
             * =========================================
             *
             * No hacemos nada con inventario porque
             * nunca se reservó ni descontó stock.
             */
            if ($venta->estado === 'pendiente') {

                $venta->update([
                    'estado' => 'cancelada',
                ]);

                return;
            }

            /*
             * =========================================
             * CASO 2
             * RESERVADA → CANCELADA
             * =========================================
             *
             * Tenemos que liberar stock_reservado.
             */

            /*
             * Primero validamos todos los productos.
             */
            foreach ($venta->detalles as $detalle) {

                $inventario = Inventario::query()
                    ->where(
                        'ubicacione_id',
                        $venta->ubicacione_id
                    )
                    ->where(
                        'producto_id',
                        $detalle->producto_id
                    )
                    ->lockForUpdate()
                    ->first();

                if (! $inventario) {

                    throw ValidationException::withMessages([
                        'inventario' =>
                            "No existe inventario para {$detalle->producto->nombre}.",
                    ]);
                }

                $cantidad =
                    (float) $detalle->cantidad;

                if (
                    $cantidad >
                    (float) $inventario->stock_reservado
                ) {

                    throw ValidationException::withMessages([
                        'stock' =>
                            "El stock reservado de {$detalle->producto->nombre} es inconsistente.",
                    ]);
                }
            }

            /*
             * Si todas las validaciones pasaron,
             * liberamos las reservas.
             */
            foreach ($venta->detalles as $detalle) {

                $inventario = Inventario::query()
                    ->where(
                        'ubicacione_id',
                        $venta->ubicacione_id
                    )
                    ->where(
                        'producto_id',
                        $detalle->producto_id
                    )
                    ->lockForUpdate()
                    ->firstOrFail();

                $inventario->decrement(
                    'stock_reservado',
                    $detalle->cantidad
                );
            }

            /*
             * Finalmente cancelamos.
             */
            $venta->update([
                'estado' => 'cancelada',
            ]);
        });
    }
}
