<?php

namespace App\Filament\Resources\Ubicaciones\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UbicacioneForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información de la ubicación')
                    ->description('Registra un almacén o una tienda.')
                    ->schema([
                        TextInput::make('nombre')
                            ->label('Nombre')
                            ->placeholder('Ej: Almacén Central')
                            ->required()
                            ->maxLength(150),

                        Select::make('tipo')
                            ->label('Tipo de ubicación')
                            ->options([
                                'almacen' => 'Almacén',
                                'tienda' => 'Tienda',
                            ])
                            ->required()
                            ->native(false),

                        TextInput::make('direccion')
                            ->label('Dirección')
                            ->placeholder('Ej: Av. 16 de Julio #123')
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Toggle::make('activo')
                            ->label('Activo')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }
}
