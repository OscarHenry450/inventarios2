<?php

namespace App\Filament\Resources\DetalleVentas\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DetalleVentaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('venta_id')
                    ->required()
                    ->numeric(),
                TextInput::make('producto_id')
                    ->required()
                    ->numeric(),
                TextInput::make('producto_precio_id')
                    ->required()
                    ->numeric(),
                TextInput::make('cantidad')
                    ->required()
                    ->numeric(),
                TextInput::make('unidades_totales')
                    ->required()
                    ->numeric(),
                TextInput::make('precio_unitario')
                    ->required()
                    ->numeric(),
                TextInput::make('descuento')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('subtotal')
                    ->required()
                    ->numeric(),
            ]);
    }
}
