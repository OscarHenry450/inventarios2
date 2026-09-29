<?php

namespace App\Filament\Resources\Ventas\Schemas;

use App\Models\ProductoPrecio;
use App\Models\Ubicacione;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

use App\Models\Inventario;
use App\Models\Producto;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
class VentaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(4)
            ->components([


                Section::make('Información de la venta')
                    ->description('Datos generales de la venta.')
                    ->columnSpan(1)
                    ->schema([
                        Select::make('ubicacione_id')
                            ->label('Tienda / Almacén')
                            ->placeholder('Seleccionar ubicación')

                            ->options(function (): array {

                                $user = auth()->user();

                                if (! $user) {
                                    return [];
                                }
                                return $user
                                    ->ubicaciones()
                                    ->where('ubicaciones.activo', true)
                                    ->orderBy('ubicaciones.tipo')
                                    ->orderBy('ubicaciones.nombre')
                                    ->get()
                                    ->mapWithKeys(function ($ubicacion) {

                                        $tipo = match ($ubicacion->tipo) {
                                            'tienda' => 'Tienda',
                                            'almacen' => 'Almacén',
                                            default => ucfirst($ubicacion->tipo),
                                        };

                                        return [
                                            $ubicacion->id =>
                                                "{$ubicacion->nombre} - {$tipo}",
                                        ];
                                    })
                                    ->toArray();
                            })

                            ->searchable()
                            ->preload()
                            ->live()
                            ->required(),
                        Select::make('cliente_id')
                            ->label('Cliente')
                            ->placeholder('Seleccionar cliente')
                            ->relationship(
                                name: 'cliente',
                                titleAttribute: 'nombre'
                            )
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        /*
                        |--------------------------------------------------------------
                        | FECHA
                        |--------------------------------------------------------------
                        */
                        DateTimePicker::make('fecha')
                            ->label('Fecha de venta')
                            ->default(now())
                            ->seconds(false)
                            ->required(),

                    ]),

                /*
                |--------------------------------------------------------------------------
                | COLUMNA DERECHA - 3/4
                |--------------------------------------------------------------------------
                */
                Section::make('Productos')
                    ->description(
                        'Productos que se incluirán en la venta.'
                    )
                    ->columnSpan(3)
                    ->schema([
                        Section::make('Productos')
                            ->description(
                                'Productos disponibles en la ubicación seleccionada.'
                            )
                            ->columnSpan(3)
                            ->schema([

                                Repeater::make('detalles')
                                    ->relationship()
                                    ->label('Detalle de productos')
                                    ->defaultItems(0)
                                    ->minItems(1)
                                    ->addActionLabel('Agregar producto')
                                    ->columns(4)
                                    ->live()
                                    ->afterStateUpdated(function (
                                        Get $get,
                                        Set $set
                                    ): void {

                                        self::recalcularTotalesVenta(
                                            $get,
                                            $set
                                        );
                                    })
                                    ->schema([
                                        Select::make('producto_id')
                                            ->label('Producto')
                                            ->placeholder('Seleccionar producto')

                                            ->options(function (Get $get): array {

                                                /*
                                                 * Obtenemos el estado de la venta.
                                                 */
                                                $estadoVenta = $get('../../');

                                                if (! is_array($estadoVenta)) {
                                                    return [];
                                                }

                                                /*
                                                 * Obtenemos la ubicación seleccionada.
                                                 */
                                                $ubicacionId =
                                                    $estadoVenta['ubicacione_id'] ?? null;

                                                if (! $ubicacionId) {
                                                    return [];
                                                }

                                                /*
                                                 * Buscamos inventarios ÚNICAMENTE
                                                 * de la ubicación seleccionada.
                                                 */
                                                return Inventario::query()

                                                    ->where(
                                                        'ubicacione_id',
                                                        $ubicacionId
                                                    )

                                                    ->whereHas('producto', function ($query) {
                                                        $query->where('estado', true);
                                                    })

                                                    ->with('producto')

                                                    ->get()

                                                    ->mapWithKeys(function (
                                                        Inventario $inventario
                                                    ): array {

                                                        if (! $inventario->producto) {
                                                            return [];
                                                        }

                                                        return [
                                                            $inventario->producto_id =>
                                                                $inventario->producto->nombre,
                                                        ];
                                                    })

                                                    ->toArray();
                                            })

                                            ->searchable()
                                            ->preload()
                                            ->live()

                                            ->afterStateUpdated(function (
                                                $state,
                                                Get $get,
                                                Set $set
                                            ): void {

                                                // Primero limpiamos el stock mostrado.
                                                $set('stock_disponible', 0);
                                                $set('cantidad', 1);
                                                $set('producto_precio_id', null);
                                                $set('precio_unitario', 0);
                                                $set('descuento', 0);
                                                $set('subtotal', 0);

                                                if (! $state) {
                                                    return;
                                                }

                                                /*
                                                 * Obtenemos los datos de la venta.
                                                 */
                                                $estadoVenta = $get('../../');

                                                if (! is_array($estadoVenta)) {
                                                    return;
                                                }

                                                /*
                                                 * Ubicación seleccionada arriba.
                                                 */
                                                $ubicacionId =
                                                    $estadoVenta['ubicacione_id'] ?? null;

                                                if (! $ubicacionId) {
                                                    return;
                                                }

                                                /*
                                                 * Buscamos exactamente:
                                                 *
                                                 * ubicación + producto
                                                 */
                                                $inventario = Inventario::query()
                                                    ->where(
                                                        'ubicacione_id',
                                                        $ubicacionId
                                                    )
                                                    ->where(
                                                        'producto_id',
                                                        $state
                                                    )
                                                    ->first();

                                                if (! $inventario) {
                                                    return;
                                                }

                                                /*
                                                 * Calculamos stock realmente disponible.
                                                 */
                                                $stockDisponible =
                                                    (float) $inventario->stock
                                                    -
                                                    (float) $inventario->stock_reservado;

                                                $set(
                                                    'stock_disponible',
                                                    max($stockDisponible, 0)
                                                );
                                            })
                                            ->required()
                                            ->columnSpan(4),
                                        TextInput::make('stock_disponible')
                                            ->label('Stock disponible')
                                            ->numeric()
                                            ->suffix('unidades')
                                            ->default(0)
                                            ->readOnly()
                                            ->dehydrated(false)
                                            ->columnSpan(1),
                                        TextInput::make('cantidad')
                                            ->label('Cantidad')
                                            ->numeric()
                                            ->minValue(1)
                                            ->step(1)
                                            ->default(1)
                                            ->required()
                                            ->live(onBlur: true)

                                            ->afterStateUpdated(function (
                                                Set $set
                                            ): void {

                                                $set('producto_precio_id', null);
                                                $set('precio_unitario', 0);
                                                $set('subtotal', 0);
                                            })

                                            ->columnSpan(2)

                                            ->rules([
                                                function (Get $get) {

                                                    return function (
                                                        string $attribute,
                                                               $value,
                                                        \Closure $fail
                                                    ) use ($get): void {

                                                        $productoId =
                                                            $get('producto_id');

                                                        if (! $productoId) {
                                                            return;
                                                        }

                                                        $estadoVenta =
                                                            $get('../../');

                                                        if (! is_array($estadoVenta)) {
                                                            return;
                                                        }

                                                        $ubicacionId =
                                                            $estadoVenta['ubicacione_id']
                                                            ?? null;

                                                        if (! $ubicacionId) {
                                                            return;
                                                        }

                                                        $inventario = Inventario::query()
                                                            ->where(
                                                                'ubicacione_id',
                                                                $ubicacionId
                                                            )
                                                            ->where(
                                                                'producto_id',
                                                                $productoId
                                                            )
                                                            ->first();

                                                        if (! $inventario) {
                                                            $fail(
                                                                'El producto no tiene inventario en esta ubicación.'
                                                            );

                                                            return;
                                                        }

                                                        $stockDisponible =
                                                            (float) $inventario->stock
                                                            -
                                                            (float) $inventario->stock_reservado;

                                                        if ((float) $value > $stockDisponible) {
                                                            $fail(
                                                                "Solo hay {$stockDisponible} unidades disponibles."
                                                            );
                                                        }
                                                    };
                                                },
                                            ]),
                                        Select::make('producto_precio_id')
                                            ->label('Precio')
                                            ->placeholder('Seleccionar precio')

                                            ->options(function (Get $get): array {

                                                $productoId = $get('producto_id');

                                                $cantidad =
                                                    (float) ($get('cantidad') ?? 0);

                                                if (! $productoId || $cantidad <= 0) {
                                                    return [];
                                                }

                                                return ProductoPrecio::query()

                                                    /*
                                                     * Precio del producto seleccionado.
                                                     */
                                                    ->where(
                                                        'producto_id',
                                                        $productoId
                                                    )

                                                    /*
                                                     * Solo precios activos.
                                                     */
                                                    ->where(
                                                        'activo',
                                                        true
                                                    )

                                                    /*
                                                     * Solo presentaciones cuyo mínimo
                                                     * ya alcanzó la cantidad vendida.
                                                     */
                                                    ->whereHas(
                                                        'cantidad',
                                                        function ($query) use ($cantidad) {

                                                            $query->where(
                                                                'cantidad_unidades',
                                                                '<=',
                                                                $cantidad
                                                            );
                                                        }
                                                    )

                                                    ->with('cantidad')

                                                    ->get()

                                                    ->sortBy(
                                                        'cantidad.cantidad_unidades'
                                                    )

                                                    ->mapWithKeys(function (
                                                        ProductoPrecio $precio
                                                    ): array {

                                                        $nombre =
                                                            $precio->cantidad->nombre;

                                                        $minimo =
                                                            $precio->cantidad
                                                                ->cantidad_unidades;

                                                        $precioUnitario =
                                                            number_format(
                                                                (float) $precio->precio_unitario,
                                                                2
                                                            );

                                                        return [
                                                            $precio->id =>
                                                                "{$nombre} - Bs {$precioUnitario} c/u (desde {$minimo})",
                                                        ];
                                                    })

                                                    ->toArray();
                                            })

                                            ->searchable()
                                            ->preload()
                                            ->live()

                                            ->afterStateUpdated(function (
                                                $state,
                                                Get $get,

                                                Set $set
                                            ): void {

                                                if (! $state) {
                                                    $set('precio_unitario', 0);
                                                    $set('subtotal', 0);

                                                    return;
                                                }

                                                $precio = ProductoPrecio::find($state);

                                                if (! $precio) {
                                                    $set('precio_unitario', 0);
                                                    $set('subtotal', 0);
                                                    return;
                                                }

                                                $precioUnitario =
                                                    (float) $precio->precio_unitario;

                                                $cantidad =
                                                    (float) ($get('cantidad') ?? 0);

                                                $descuento =
                                                    (float) ($get('descuento') ?? 0);

                                                $subtotal =
                                                    ($cantidad * $precioUnitario)
                                                    - $descuento;

                                                $set(
                                                    'precio_unitario',
                                                    $precioUnitario
                                                );

                                                $set(
                                                    'subtotal',
                                                    max($subtotal, 0)
                                                );
                                                self::recalcularTotalesDesdeDetalle(
                                                    $get,
                                                    $set
                                                );
                                            })

                                            ->required()
                                            ->columnSpan(4),
                                        TextInput::make('precio_unitario')
                                            ->label('Precio unitario')
                                            ->prefix('Bs')
                                            ->numeric()
                                            ->default(0)
                                            ->readOnly()
                                            ->required()
                                            ->columnSpan(3),
                                        TextInput::make('descuento')
                                            ->label('Descuento')
                                            ->prefix('Bs')
                                            ->numeric()
                                            ->minValue(0)
                                            ->default(0)
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (
                                                $state,
                                                Get $get,
                                                Set $set
                                            ): void {

                                                $cantidad =
                                                    (float) ($get('cantidad') ?? 0);

                                                $precioUnitario =
                                                    (float) ($get('precio_unitario') ?? 0);

                                                $descuento =
                                                    (float) ($state ?? 0);

                                                $subtotalBruto =
                                                    $cantidad * $precioUnitario;

                                                $subtotal =
                                                    $subtotalBruto - $descuento;

                                                $set(
                                                    'subtotal',
                                                    max($subtotal, 0)
                                                );
                                                self::recalcularTotalesDesdeDetalle(
                                                    $get,
                                                    $set
                                                );
                                            })
                                            ->rules([
                                                function (Get $get) {

                                                    return function (
                                                        string $attribute,
                                                               $value,
                                                        \Closure $fail
                                                    ) use ($get): void {

                                                        $cantidad =
                                                            (float) ($get('cantidad') ?? 0);

                                                        $precioUnitario =
                                                            (float) ($get('precio_unitario') ?? 0);

                                                        $descuento =
                                                            (float) ($value ?? 0);

                                                        $importe =
                                                            $cantidad * $precioUnitario;

                                                        if ($descuento > $importe) {
                                                            $fail(
                                                                'El descuento no puede ser mayor al importe del producto.'
                                                            );
                                                        }
                                                    };
                                                },
                                            ])
                                            ->columnSpan(4),
                                        TextInput::make('subtotal')
                                            ->label('Subtotal')
                                            ->prefix('Bs')
                                            ->numeric()
                                            ->default(0)
                                            ->readOnly()
                                            ->required()
                                            ->columnSpan(4),
                                    ]),
                                Section::make('Totales de la venta')
                                    ->columns(3)
                                    ->schema([

                                        TextInput::make('subtotal')
                                            ->label('Subtotal')
                                            ->prefix('Bs')
                                            ->numeric()
                                            ->default(0)
                                            ->readOnly()
                                            ->required(),

                                        TextInput::make('descuento')
                                            ->label('Descuento general')
                                            ->prefix('Bs')
                                            ->numeric()
                                            ->minValue(0)
                                            ->default(0)
                                            ->required()
                                            ->live(onBlur: true)

                                            ->afterStateUpdated(function (
                                                $state,
                                                Get $get,
                                                Set $set
                                            ): void {

                                                $subtotal =
                                                    (float) ($get('subtotal') ?? 0);

                                                $descuento =
                                                    (float) ($state ?? 0);

                                                $total =
                                                    $subtotal - $descuento;

                                                $set(
                                                    'total',
                                                    round(
                                                        max($total, 0),
                                                        2
                                                    )
                                                );
                                            })

                                            ->rules([
                                                function (Get $get) {

                                                    return function (
                                                        string $attribute,
                                                               $value,
                                                        \Closure $fail
                                                    ) use ($get): void {

                                                        $subtotal =
                                                            (float) ($get('subtotal') ?? 0);

                                                        $descuento =
                                                            (float) ($value ?? 0);

                                                        if ($descuento > $subtotal) {
                                                            $fail(
                                                                'El descuento general no puede ser mayor al subtotal de la venta.'
                                                            );
                                                        }
                                                    };
                                                },
                                            ]),

                                        TextInput::make('total')
                                            ->label('Total')
                                            ->prefix('Bs')
                                            ->numeric()
                                            ->default(0)
                                            ->readOnly()
                                            ->required(),

                                    ]),
                            ]),
                    ]),
            ]);
    }
    private static function recalcularTotalesVenta(
        Get $get,
        Set $set
    ): void {

        $detalles = $get('detalles') ?? [];

        $subtotalVenta = 0;

        foreach ($detalles as $detalle) {
            $subtotalVenta +=
                (float) ($detalle['subtotal'] ?? 0);
        }

        $descuentoGeneral =
            (float) ($get('descuento') ?? 0);

        $total =
            $subtotalVenta - $descuentoGeneral;

        $set(
            'subtotal',
            round($subtotalVenta, 2)
        );

        $set(
            'total',
            round(max($total, 0), 2)
        );
    }
    private static function recalcularTotalesDesdeDetalle(
        Get $get,
        Set $set
    ): void {

        $estadoVenta = $get('../../');

        if (! is_array($estadoVenta)) {
            return;
        }

        $detalles =
            $estadoVenta['detalles'] ?? [];

        $subtotalVenta = 0;

        foreach ($detalles as $detalle) {
            $subtotalVenta +=
                (float) ($detalle['subtotal'] ?? 0);
        }

        $descuentoGeneral =
            (float) ($estadoVenta['descuento'] ?? 0);

        $set(
            '../../subtotal',
            round($subtotalVenta, 2)
        );

        $set(
            '../../total',
            round(
                max(
                    $subtotalVenta - $descuentoGeneral,
                    0
                ),
                2
            )
        );
    }
}
