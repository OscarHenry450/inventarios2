<?php

namespace App\Filament\Resources\Transferencias\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class TransferenciaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Información de la transferencia')
                    ->schema([

                        TextInput::make('numero')
                            ->label('N° Transferencia')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),

                        DateTimePicker::make('fecha_solicitud')
                            ->label('Fecha de solicitud')
                            ->default(now())
                            ->required(),

                        Select::make('ubicacion_origen_id')
                            ->label('Ubicación de origen')
                            ->relationship(
                                'ubicacionOrigen',
                                'nombre'
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->live(),

                        Select::make('ubicacion_destino_id')
                            ->label('Ubicación de destino')
                            ->relationship(
                                'ubicacionDestino',
                                'nombre'
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->live()
                            ->rule(function (Get $get) {
                                return function (
                                    string $attribute,
                                           $value,
                                    \Closure $fail
                                ) use ($get) {

                                    if (
                                        $value &&
                                        $value == $get('ubicacion_origen_id')
                                    ) {
                                        $fail(
                                            'La ubicación de destino debe ser diferente de la ubicación de origen.'
                                        );
                                    }
                                };
                            }),

                        Select::make('usuario_recibe_id')
                            ->label('Usuario que recibe')
                            ->relationship(
                                'usuarioRecibe',
                                'name'
                            )
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->native(false),

                        Select::make('estado')
                            ->label('Estado')
                            ->options([
                                'pendiente' => 'Pendiente',
                                'preparando' => 'Preparando',
                                'en_transito' => 'En tránsito',
                                'completada' => 'Completada',
                                'cancelada' => 'Cancelada',
                            ])
                            ->default('pendiente')
                            ->disabled()
                            ->dehydrated()
                            ->native(false),

                        Textarea::make('observacion')
                            ->label('Observación')
                            ->rows(4)
                            ->columnSpanFull(),

                    ])
                    ->columns(2),
            ]);
    }
}
