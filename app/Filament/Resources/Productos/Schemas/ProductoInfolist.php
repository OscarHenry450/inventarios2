<?php

namespace App\Filament\Resources\Productos\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductoInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del producto')
                    ->schema([
                        TextEntry::make('nombre')
                            ->label('Nombre'),

                        TextEntry::make('marca.nombre')
                            ->label('Marca')
                            ->placeholder('Sin marca'),

                        TextEntry::make('modelo')
                            ->label('Modelo')
                            ->placeholder('Sin modelo'),

                        IconEntry::make('estado')
                            ->label('Estado')
                            ->boolean(),

                        TextEntry::make('descripcion')
                            ->label('Descripción')
                            ->placeholder('Sin descripción')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Imagen del producto')
                    ->schema([
                        ImageEntry::make('foto')
                            ->label('Imagen')
                            ->disk('public')
                            ->height(250),
                    ]),

                Section::make('Información del registro')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Fecha de creación')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('-'),

                        TextEntry::make('updated_at')
                            ->label('Última actualización')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('-'),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }
}
