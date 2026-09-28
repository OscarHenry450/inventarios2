<?php

namespace App\Filament\Resources\Ventas\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class VentaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Información de la venta')
                    ->schema([

                        TextInput::make('numero')
                            ->label('N.º Venta')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),

                        Select::make('ubicacione_id')
                            ->label('Tienda / Ubicación')
                            ->relationship(
                                name: 'ubicacion',
                                titleAttribute: 'nombre',
                                modifyQueryUsing: fn (Builder $query) =>
                                $query
                                    ->where('activo', true)
                                    ->where('tipo', 'tienda')
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),

                        Select::make('cliente_id')
                            ->label('Cliente')
                            ->relationship('cliente', 'nombre')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->native(false),

                        DateTimePicker::make('fecha')
                            ->label('Fecha')
                            ->default(now())
                            ->required(),

                    ])
                    ->columns(2),

                Section::make('Totales')
                    ->schema([

                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->prefix('Bs')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->dehydrated(),

                        TextInput::make('descuento')
                            ->label('Descuento general')
                            ->prefix('Bs')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),

                        TextInput::make('total')
                            ->label('Total')
                            ->prefix('Bs')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->dehydrated(),

                        TextInput::make('estado')
                            ->label('Estado')
                            ->default('pendiente')
                            ->disabled()
                            ->dehydrated(),

                    ])
                    ->columns(2),

                Section::make('Observación')
                    ->schema([

                        Textarea::make('observacion')
                            ->label('Observación')
                            ->rows(3)
                            ->columnSpanFull(),

                    ]),
            ]);
    }
}
