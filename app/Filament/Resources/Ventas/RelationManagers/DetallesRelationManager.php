<?php

namespace App\Filament\Resources\Ventas\RelationManagers;

use App\Models\Inventario;
use App\Models\ProductoPrecio;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Closure;
class DetallesRelationManager extends RelationManager
{
    protected static string $relationship = 'detalles';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('producto_id')
                    ->label('Producto')
                    ->relationship('producto', 'nombre')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->native(false)
                    ->live()
                    ->afterStateUpdated(function (
                        $state,
                        Set $set
                    ): void {

                        // Reiniciar la presentación
                        $set('producto_precio_id', null);

                        $set('precio_unitario', 0);

                        $set('cantidad', 1);

                        $set('unidades_totales', 0);

                        $set('subtotal', 0);

                        if (! $state) {
                            $set('stock_disponible', 0);

                            return;
                        }

                        $set(
                            'stock_disponible',
                            $this->obtenerStockDisponible(
                                (int) $state
                            )
                        );
                    }),
                Select::make('producto_precio_id')
                    ->label('Presentación')
                    ->options(function (Get $get): array {

                        $productoId = $get('producto_id');

                        if (! $productoId) {
                            return [];
                        }

                        return ProductoPrecio::query()
                            ->with('cantidad')
                            ->where(
                                'producto_id',
                                $productoId
                            )
                            ->where('activo', true)
                            ->get()
                            ->mapWithKeys(
                                function (ProductoPrecio $precio): array {

                                    return [
                                        $precio->id =>
                                            $precio->cantidad->nombre
                                            . ' - Bs '
                                            . number_format(
                                                (float) $precio->precio_unitario,
                                                2
                                            ),
                                    ];
                                }
                            )
                            ->toArray();
                    })
                    ->searchable()
                    ->required()
                    ->native(false)
                    ->live()
                    ->afterStateUpdated(function (
                        $state,
                        Get $get,
                        Set $set
                    ): void {

                        if (! $state) {
                            $set('precio_unitario', 0);
                            $set('unidades_totales', 0);
                            $set('subtotal', 0);

                            return;
                        }

                        $precio = ProductoPrecio::query()
                            ->with('cantidad')
                            ->find($state);

                        if (! $precio) {
                            return;
                        }

                        $set(
                            'precio_unitario',
                            $precio->precio_unitario
                        );

                        $cantidad = (float) (
                        $get('cantidad') ?: 1
                        );

                        $unidades =
                            $cantidad
                            * (float) $precio
                                ->cantidad
                                ->cantidad_unidades;

                        $set(
                            'unidades_totales',
                            $unidades
                        );

                        $subtotal =
                            ($cantidad
                                * (float) $precio->precio_unitario)
                            - (float) ($get('descuento') ?: 0);

                        $set(
                            'subtotal',
                            max(0, $subtotal)
                        );
                    }),
                TextInput::make('stock_disponible')
                    ->label('Stock disponible')
                    ->numeric()
                    ->default(0)
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('cantidad')
                    ->label('Cantidad')
                    ->numeric()
                    ->minValue(1)
                    ->default(1)
                    ->required()
                    ->live()
                    ->afterStateUpdated(
                        function (
                            $state,
                            Get $get,
                            Set $set
                        ): void {
                            $this->calcularDetalle(
                                $get,
                                $set
                            );
                        }
                    )
                    ->rule(function (Get $get) {

                        return function (
                            string $attribute,
                                   $value,
                            Closure $fail
                        ) use ($get): void {

                            $productoPrecioId =
                                $get('producto_precio_id');

                            $productoId =
                                $get('producto_id');

                            if (
                                ! $productoPrecioId
                                || ! $productoId
                            ) {
                                return;
                            }

                            $precio = ProductoPrecio::query()
                                ->with('cantidad')
                                ->find($productoPrecioId);

                            if (! $precio) {
                                return;
                            }

                            $unidadesNecesarias =
                                (float) $value
                                * (float) $precio
                                    ->cantidad
                                    ->cantidad_unidades;

                            $disponible =
                                $this->obtenerStockDisponible(
                                    (int) $productoId
                                );

                            if (
                                $unidadesNecesarias
                                > $disponible
                            ) {
                                $fail(
                                    "Stock insuficiente. Necesita {$unidadesNecesarias} unidades y solo existen {$disponible} disponibles."
                                );
                            }
                        };
                    }),

                TextInput::make('unidades_totales')
                    ->label('Unidades físicas')
                    ->numeric()
                    ->default(0)
                    ->disabled()
                    ->dehydrated(),
                TextInput::make('descuento')
                    ->label('Descuento')
                    ->prefix('Bs')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->live()
                    ->afterStateUpdated(
                        function (
                            Get $get,
                            Set $set
                        ): void {
                            $this->calcularDetalle(
                                $get,
                                $set
                            );
                        }
                    ),
                TextInput::make('subtotal')
                    ->label('Subtotal')
                    ->prefix('Bs')
                    ->numeric()
                    ->default(0)
                    ->disabled()
                    ->dehydrated(),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('cantidad')
            ->columns([
                TextColumn::make('producto.nombre')
                    ->label('Producto')
                    ->searchable(),

                TextColumn::make(
                    'productoPrecio.cantidad.nombre'
                )
                    ->label('Presentación'),

                TextColumn::make('cantidad')
                    ->label('Cantidad')
                    ->numeric(decimalPlaces: 2),

                TextColumn::make('unidades_totales')
                    ->label('Unidades')
                    ->numeric(decimalPlaces: 2),

                TextColumn::make('precio_unitario')
                    ->label('Precio')
                    ->money('BOB'),

                TextColumn::make('descuento')
                    ->label('Descuento')
                    ->money('BOB'),

                TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('BOB'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Agregar producto')
                    ->visible(
                        fn (): bool =>
                            $this->getOwnerRecord()
                                ->estado === 'pendiente'
                    )
                    ->after(
                        fn () =>
                        $this->actualizarTotalVenta()
                    ),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(
                        fn (): bool =>
                            $this->getOwnerRecord()
                                ->estado === 'pendiente'
                    )
                    ->after(
                        fn () =>
                        $this->actualizarTotalVenta()
                    ),

                DeleteAction::make()
                    ->visible(
                        fn (): bool =>
                            $this->getOwnerRecord()
                                ->estado === 'pendiente'
                    )
                    ->after(
                        fn () =>
                        $this->actualizarTotalVenta()
                    ),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
    private function obtenerStockDisponible(
        int $productoId
    ): float {

        $venta = $this->getOwnerRecord();

        $inventario = Inventario::query()
            ->where(
                'producto_id',
                $productoId
            )
            ->where(
                'ubicacione_id',
                $venta->ubicacione_id
            )
            ->first();

        if (! $inventario) {
            return 0;
        }

        return max(
            0,
            (float) $inventario->stock
            - (float) $inventario->stock_reservado
        );
    }
    private function calcularDetalle(
        Get $get,
        Set $set
    ): void {

        $productoPrecioId =
            $get('producto_precio_id');

        if (! $productoPrecioId) {
            $set('unidades_totales', 0);
            $set('subtotal', 0);

            return;
        }

        $precio = ProductoPrecio::query()
            ->with('cantidad')
            ->find($productoPrecioId);

        if (! $precio) {
            return;
        }

        $cantidad =
            (float) ($get('cantidad') ?: 0);

        $descuento =
            (float) ($get('descuento') ?: 0);

        /*
         * Unidades físicas:
         *
         * 2 docenas × 12 = 24
         */
        $unidadesTotales =
            $cantidad
            * (float) $precio
                ->cantidad
                ->cantidad_unidades;

        /*
         * Subtotal:
         *
         * 2 × Bs 240 = Bs 480
         * - descuento
         */
        $subtotal =
            ($cantidad
                * (float) $precio->precio_unitario)
            - $descuento;

        $set(
            'precio_unitario',
            $precio->precio_unitario
        );

        $set(
            'unidades_totales',
            $unidadesTotales
        );

        $set(
            'subtotal',
            max(0, $subtotal)
        );
    }
    private function actualizarTotalVenta(): void
    {
        $venta = $this->getOwnerRecord();

        $subtotal = (float) $venta
            ->detalles()
            ->sum('subtotal');

        $descuento =
            (float) $venta->descuento;

        $total = max(
            0,
            $subtotal - $descuento
        );

        $venta->update([
            'subtotal' => $subtotal,
            'total' => $total,
        ]);
    }
}
