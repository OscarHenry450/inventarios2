<?php

namespace App\Filament\Resources\Compras\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CompraForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información de la compra')
                    ->schema([
                        TextInput::make('numero')
                            ->label('Número de compra')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),

                        TextInput::make('proveedor')
                            ->label('Proveedor')
                            ->required()
                            ->maxLength(150),

                        Select::make('ubicacione_id')
                            ->label('Ubicación destino')
                            ->relationship('ubicacion', 'nombre')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),

                        DateTimePicker::make('fecha')
                            ->label('Fecha de compra')
                            ->default(now())
                            ->required(),

                        TextInput::make('numero_documento')
                            ->label('Número de documento')
                            ->placeholder('Factura, recibo, nota...')
                            ->maxLength(100),

                        Textarea::make('observaciones')
                            ->label('Observaciones')
                            ->rows(3)
                            ->columnSpanFull(),

                        TextInput::make('total')
                            ->label('Total')
                            ->prefix('Bs')
                            ->numeric()
                            ->default(0)
                            ->readOnly()
                            ->dehydrated(),

                        Select::make('estado')
                            ->label('Estado')
                            ->options([
                                'pendiente' => 'Pendiente',
                                'confirmada' => 'Confirmada',
                                'cancelada' => 'Cancelada',
                            ])
                            ->default('pendiente')
                            ->disabled()
                            ->dehydrated()
                            ->native(false),
                    ])
                    ->columns(2),
            ]);
    }
}
