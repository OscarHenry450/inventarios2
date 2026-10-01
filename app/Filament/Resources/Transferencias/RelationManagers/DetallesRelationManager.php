<?php

namespace App\Filament\Resources\Transferencias\RelationManagers;

use App\Models\DetalleTransferencia;
use App\Models\Inventario;
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
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Closure;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Database\Eloquent\Model;
class DetallesRelationManager extends RelationManager
{
    protected static string $relationship = 'detalles';

    protected static ?string $title = 'Productos de la transferencia';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                /*
                 * PRODUCTOS DISPONIBLES EN EL ORIGEN
                 */
                Select::make('producto_id')
                    ->label('Producto')
                    ->options(function (): array {

                        $transferencia = $this->getOwnerRecord();

                        // Comprobar que el usuario puede usar el origen.
                        $permitido = auth()->user()
                            ->ubicaciones()
                            ->where(
                                'ubicaciones.id',
                                $transferencia->ubicacion_origen_id
                            )
                            ->exists();

                        if (! $permitido) {
                            return [];
                        }

                        return Inventario::query()
                            ->with('producto')
                            ->where(
                                'ubicacione_id',
                                $transferencia->ubicacion_origen_id
                            )
                            ->whereColumn(
                                'stock',
                                '>',
                                'stock_reservado'
                            )
                            ->get()
                            ->filter(fn ($inventario) =>
                                $inventario->producto !== null
                            )
                            ->mapWithKeys(fn ($inventario) => [
                                $inventario->producto_id =>
                                    $inventario->producto->nombre,
                            ])
                            ->toArray();
                    })
                    ->searchable()
                    ->required()
                    ->native(false)
                    ->live()
                    ->disabled(fn (?Model $record): bool =>
                        $record !== null
                    )
                    ->dehydrated()
                    ->afterStateUpdated(function (
                        $state,
                        Set $set
                    ): void {

                        $set('cantidad_solicitada', null);

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
                    })
                    ->rule(function (?Model $record) {

                        return function (
                            string $attribute,
                                   $value,
                            Closure $fail
                        ) use ($record): void {

                            if (! $value) {
                                return;
                            }

                            $existe = DetalleTransferencia::query()
                                ->where(
                                    'transferencia_id',
                                    $this->getOwnerRecord()->id
                                )
                                ->where('producto_id', $value)
                                ->when(
                                    $record,
                                    fn ($query) =>
                                    $query->whereKeyNot($record->id)
                                )
                                ->exists();

                            if ($existe) {
                                $fail(
                                    'Este producto ya fue agregado a la transferencia.'
                                );
                            }

                            $inventario = Inventario::query()
                                ->where('producto_id', $value)
                                ->where(
                                    'ubicacione_id',
                                    $this->getOwnerRecord()
                                        ->ubicacion_origen_id
                                )
                                ->first();

                            if (! $inventario) {
                                $fail(
                                    'El producto no pertenece al inventario de origen.'
                                );
                            }
                        };
                    }),

                /*
                 * STOCK DISPONIBLE
                 */
                TextInput::make('stock_disponible')
                    ->label('Stock disponible en origen')
                    ->numeric()
                    ->default(0)
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText(
                        'Stock físico menos stock reservado.'
                    ),

                /*
                 * CANTIDAD SOLICITADA
                 */
                TextInput::make('cantidad_solicitada')
                    ->label('Cantidad a transferir')
                    ->numeric()
                    ->required()

                    ->step(1)
                    ->live(onBlur: true)
                    ->rule(function (
                        Get $get,
                        ?Model $record
                    ) {

                        return function (
                            string $attribute,
                                   $value,
                            Closure $fail
                        ) use ($get, $record): void {

                            $productoId = $get('producto_id');

                            if (! $productoId) {
                                return;
                            }

                            // Seguridad: validar permisos también al guardar.
                            $transferencia = $this->getOwnerRecord();

                            $permitido = auth()->user()
                                ->ubicaciones()
                                ->where(
                                    'ubicaciones.id',
                                    $transferencia->ubicacion_origen_id
                                )
                                ->exists();

                            if (! $permitido) {
                                $fail(
                                    'No tienes permiso para transferir desde esta ubicación.'
                                );

                                return;
                            }

                            $disponible =
                                $this->obtenerStockDisponible(
                                    (int) $productoId
                                );

                            if ((float) $value > $disponible) {

                                $fail(
                                    "Stock insuficiente. Disponible: {$disponible} unidades."
                                );
                            }
                        };
                    }),

            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('producto.nombre')
                    ->label('Producto')
                    ->searchable(),

                TextColumn::make('cantidad_solicitada')
                    ->label('Solicitada')
                    ->numeric(decimalPlaces: 2),

                TextColumn::make('cantidad_enviada')
                    ->label('Enviada')
                    ->numeric(decimalPlaces: 2)
                    ->placeholder('Pendiente'),

                TextColumn::make('cantidad_recibida')
                    ->label('Recibida')
                    ->numeric(decimalPlaces: 2)
                    ->placeholder('Pendiente'),

            ])

            ->headerActions([

                CreateAction::make()
                    ->label('Agregar producto')
                    ->visible(fn (): bool =>
                    $this->puedeModificar()
                    ),

            ])

            ->recordActions([

                EditAction::make()
                    ->visible(fn (): bool =>
                    $this->puedeModificar()
                    ),

                DeleteAction::make()
                    ->requiresConfirmation()
                    ->visible(fn (): bool =>
                    $this->puedeModificar()
                    ),

            ]);
    }

    /*
     * OBTENER STOCK DISPONIBLE DEL ORIGEN
     */
    private function obtenerStockDisponible(
        int $productoId
    ): float {

        $transferencia = $this->getOwnerRecord();

        $inventario = Inventario::query()
            ->where('producto_id', $productoId)
            ->where(
                'ubicacione_id',
                $transferencia->ubicacion_origen_id
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

    /*
     * SOLO SE MODIFICAN TRANSFERENCIAS PENDIENTES
     * Y CUYO ORIGEN PERTENECE AL USUARIO.
     */
    private function puedeModificar(): bool
    {
        $transferencia = $this->getOwnerRecord();

        /*
         * Ya procesada.
         */
        if ($transferencia->estado !== 'pendiente') {
            return false;
        }

        /*
         * Ya enviada al destinatario.
         */
        if ($transferencia->fecha_envio !== null) {
            return false;
        }

        /*
         * Solamente el creador puede modificarla.
         */
        if (
            (int) $transferencia->user_id
            !== (int) auth()->id()
        ) {
            return false;
        }

        /*
         * Debe continuar teniendo acceso al origen.
         */
        return auth()->user()
            ->ubicaciones()
            ->where(
                'ubicaciones.id',
                $transferencia->ubicacion_origen_id
            )
            ->exists();
    }
}
