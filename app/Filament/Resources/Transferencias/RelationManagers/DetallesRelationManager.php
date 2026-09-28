<?php

namespace App\Filament\Resources\Transferencias\RelationManagers;

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
                    ->afterStateUpdated(function ($state, Set $set): void {

                        if (! $state) {
                            $set('stock_disponible', 0);

                            return;
                        }

                        $stockDisponible = $this->obtenerStockDisponible(
                            (int) $state
                        );

                        $set(
                            'stock_disponible',
                            $stockDisponible
                        );
                    }),

                TextInput::make('stock_disponible')
                    ->label('Stock disponible en origen')
                    ->numeric()
                    ->default(0)
                    ->disabled()
                    ->dehydrated(false),

                TextInput::make('cantidad_solicitada')
                    ->label('Cantidad solicitada')
                    ->numeric()
                    ->minValue(0.01)
                    ->required()
                    ->rule(function (Get $get) {

                        return function (
                            string $attribute,
                                   $value,
                            Closure $fail
                        ) use ($get): void {

                            $productoId = $get('producto_id');

                            if (! $productoId) {
                                return;
                            }

                            $disponible =
                                $this->obtenerStockDisponible(
                                    (int) $productoId
                                );

                            if ((float) $value > $disponible) {
                                $fail(
                                    "Stock insuficiente. Disponible: {$disponible}."
                                );
                            }
                        };
                    }),

                TextInput::make('cantidad_enviada')
                    ->label('Cantidad enviada')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(),

                TextInput::make('cantidad_recibida')
                    ->label('Cantidad recibida')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('producto_id')
            ->columns([
                TextColumn::make('producto.nombre')
                    ->label('Producto')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('cantidad_solicitada')
                    ->label('Solicitado')
                    ->numeric(decimalPlaces: 2),

                TextColumn::make('cantidad_enviada')
                    ->label('Enviado')
                    ->numeric(decimalPlaces: 2)
                    ->placeholder('-'),

                TextColumn::make('cantidad_recibida')
                    ->label('Recibido')
                    ->numeric(decimalPlaces: 2)
                    ->placeholder('-'),
            ])

            ->headerActions([
                CreateAction::make()
                    ->label('Agregar producto')
                    ->visible(
                        fn (): bool =>
                            $this->getOwnerRecord()->estado === 'pendiente'
                    ),
            ])

            ->recordActions([
                EditAction::make()
                    ->visible(
                        fn (): bool =>
                            $this->getOwnerRecord()->estado === 'pendiente'
                    ),

                DeleteAction::make()
                    ->visible(
                        fn (): bool =>
                            $this->getOwnerRecord()->estado === 'pendiente'
                    ),
            ]);
    }
    private function obtenerStockDisponible(int $productoId): float
    {
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
}
