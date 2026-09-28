<?php

namespace App\Filament\Resources\Inventarios\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class InventarioForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('producto_id')
                    ->required()
                    ->numeric(),
                TextInput::make('ubicacione_id')
                    ->required()
                    ->numeric(),
                TextInput::make('stock')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('stock_reservado')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('stock_minimo')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
