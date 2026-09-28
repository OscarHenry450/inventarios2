<?php

namespace App\Filament\Resources\DetalleCompras\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DetalleCompraForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('compra_id')
                    ->required()
                    ->numeric(),
                TextInput::make('producto_id')
                    ->required()
                    ->numeric(),
                TextInput::make('cantidad')
                    ->required()
                    ->numeric(),
                TextInput::make('precio_compra')
                    ->required()
                    ->numeric(),
                TextInput::make('subtotal')
                    ->required()
                    ->numeric(),
            ]);
    }
}
