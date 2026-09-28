<?php

namespace App\Filament\Resources\Compras\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

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
                    ->createOptionForm([
                        TextInput::make('nombre')
                            ->label('Nombre')
                            ->required(),

                        Select::make('marca_id')
                            ->label('Marca')
                            ->relationship('marca', 'nombre')
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('modelo')
                            ->label('Modelo'),

                        Textarea::make('descripcion')
                            ->label('Descripción'),

                        Toggle::make('estado')
                            ->label('Activo')
                            ->default(true),
                    ]),
                TextInput::make('cantidad')
                    ->label('Cantidad')
                    ->numeric()
                    ->minValue(1)
                    ->default(1)
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($get, $set) {
                        self::calcularSubtotal($get, $set);
                    }),

                TextInput::make('precio_compra')
                    ->label('Precio de compra')
                    ->numeric()
                    ->minValue(0)
                    ->prefix('Bs')
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($get, $set) {
                        self::calcularSubtotal($get, $set);
                    }),
                TextInput::make('subtotal')
                    ->label('Subtotal')
                    ->numeric()
                    ->prefix('Bs')
                    ->default(0)
                    ->readOnly()
                    ->dehydrated(),
            ]);
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

                TextColumn::make('producto.marca.nombre')
                    ->label('Marca')
                    ->placeholder('Sin marca'),

                TextColumn::make('cantidad')
                    ->label('Cantidad')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('precio_compra')
                    ->label('Precio compra')
                    ->money('BOB')
                    ->sortable(),

                TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('BOB')
                    ->sortable(),
            ])

            ->headerActions([
                CreateAction::make()
                    ->label('Agregar producto')
                    ->after(function () {
                        $this->actualizarTotalCompra();
                    })
                    ->visible(
                        fn (): bool =>
                            $this->getOwnerRecord()->estado === 'pendiente'
                    )->mutateDataUsing(function (array $data): array {
                        $data['subtotal'] =
                            (float) $data['cantidad']
                            * (float) $data['precio_compra'];

                        return $data;
                    }),

            ])

            ->recordActions([
                EditAction::make()
                    ->visible(
                        fn (): bool =>
                            $this->getOwnerRecord()->estado === 'pendiente'
                    )
                    ->mutateDataUsing(function (array $data): array {
                        $data['subtotal'] =
                            (float) $data['cantidad']
                            * (float) $data['precio_compra'];

                        return $data;
                    })
                    ->after(function () {
                        $this->actualizarTotalCompra();
                    }),

                DeleteAction::make()
                    ->visible(
                        fn (): bool =>
                            $this->getOwnerRecord()->estado === 'pendiente'
                    )
                    ->after(function () {
                        $this->actualizarTotalCompra();
                    }),
            ]);
    }
    private static function calcularSubtotal($get, $set): void
    {
        $cantidad = (float) ($get('cantidad') ?? 0);

        $precioCompra = (float) ($get('precio_compra') ?? 0);

        $subtotal = $cantidad * $precioCompra;

        $set('subtotal', round($subtotal, 2));
    }
    private function actualizarTotalCompra(): void
    {
        $compra = $this->getOwnerRecord();

        $total = $compra->detalles()
            ->sum('subtotal');

        $compra->update([
            'total' => $total,
        ]);
    }
}
