<?php

namespace App\Filament\Resources\DetalleVentas\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class DetalleVentaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('venta_id')
                    ->numeric(),
                TextEntry::make('producto_id')
                    ->numeric(),
                TextEntry::make('producto_precio_id')
                    ->numeric(),
                TextEntry::make('cantidad')
                    ->numeric(),
                TextEntry::make('unidades_totales')
                    ->numeric(),
                TextEntry::make('precio_unitario')
                    ->numeric(),
                TextEntry::make('descuento')
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
