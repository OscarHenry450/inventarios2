<?php

namespace App\Filament\Resources\DetalleTransferencias\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DetalleTransferenciaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('transferencia_id')
                    ->required()
                    ->numeric(),
                TextInput::make('producto_id')
                    ->required()
                    ->numeric(),
                TextInput::make('cantidad_solicitada')
                    ->required()
                    ->numeric(),
                TextInput::make('cantidad_enviada')
                    ->numeric(),
                TextInput::make('cantidad_recibida')
                    ->numeric(),
            ]);
    }
}
