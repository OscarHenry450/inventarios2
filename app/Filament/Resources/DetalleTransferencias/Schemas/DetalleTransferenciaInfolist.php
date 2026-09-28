<?php

namespace App\Filament\Resources\DetalleTransferencias\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class DetalleTransferenciaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('transferencia_id')
                    ->numeric(),
                TextEntry::make('producto_id')
                    ->numeric(),
                TextEntry::make('cantidad_solicitada')
                    ->numeric(),
                TextEntry::make('cantidad_enviada')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('cantidad_recibida')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
