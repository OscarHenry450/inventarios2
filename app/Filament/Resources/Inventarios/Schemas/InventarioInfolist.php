<?php

namespace App\Filament\Resources\Inventarios\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class InventarioInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('producto_id')
                    ->numeric(),
                TextEntry::make('ubicacione_id')
                    ->numeric(),
                TextEntry::make('stock')
                    ->numeric(),
                TextEntry::make('stock_reservado')
                    ->numeric(),
                TextEntry::make('stock_minimo')
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
