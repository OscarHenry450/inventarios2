<?php

namespace App\Filament\Resources\DetalleCompras\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class DetalleCompraInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('compra_id')
                    ->numeric(),
                TextEntry::make('producto_id')
                    ->numeric(),
                TextEntry::make('cantidad')
                    ->numeric(),
                TextEntry::make('precio_compra')
                    ->numeric(),
                TextEntry::make('subtotal')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
