<?php

namespace App\Filament\Resources\Cantidades\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CantidadeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información de la presentación')
                    ->schema([

                        TextInput::make('nombre')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('Ej. Unidad, Docena, Caja x24'),

                        TextInput::make('cantidad_unidades')
                            ->label('Cantidad de unidades')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->required()
                            ->helperText(
                                'Indica cuántas unidades físicas representa esta presentación.'
                            ),

                        Toggle::make('activo')
                            ->label('Activo')
                            ->default(true),

                    ])
                    ->columns(2),
            ]);
    }
}
