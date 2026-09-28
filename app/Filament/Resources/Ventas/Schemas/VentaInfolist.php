<?php

namespace App\Filament\Resources\Ventas\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class VentaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('numero'),
                TextEntry::make('ubicacione_id')
                    ->numeric(),
                TextEntry::make('cliente_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('user_id')
                    ->numeric(),
                TextEntry::make('estado'),
                TextEntry::make('fecha')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('fecha_reserva')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('fecha_vencimiento_reserva')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('fecha_venta')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('subtotal')
                    ->numeric(),
                TextEntry::make('descuento')
                    ->numeric(),
                TextEntry::make('total')
                    ->numeric(),
                TextEntry::make('observacion')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
