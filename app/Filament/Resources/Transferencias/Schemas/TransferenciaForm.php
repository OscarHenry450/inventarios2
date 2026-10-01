<?php

namespace App\Filament\Resources\Transferencias\Schemas;

use App\Models\Ubicacione;
use App\Models\User;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Filament\Schemas\Components\Utilities\Set;
class TransferenciaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Información de transferencia')
                    ->schema([

                        TextInput::make('numero')
                            ->label('N.º Transferencia')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),

                        DateTimePicker::make('fecha_solicitud')
                            ->label('Fecha de solicitud')
                            ->default(now())
                            ->disabled()
                            ->dehydrated(),

                    ])
                    ->columns(2),

                Section::make('Origen y destino')
                    ->description(
                        'Seleccione desde qué ubicación enviará los productos.'
                    )
                    ->schema([

                        /*
                         * ORIGEN:
                         * SOLO UBICACIONES DEL USUARIO LOGUEADO.
                         */
                        Select::make('ubicacion_origen_id')
                            ->label('Tienda / Almacén de origen')
                            ->options(function (): array {

                                $user = auth()->user();

                                if (! $user) {
                                    return [];
                                }

                                return $user->ubicaciones()
                                    ->where('ubicaciones.activo', true)
                                    ->pluck(
                                        'ubicaciones.nombre',
                                        'ubicaciones.id'
                                    )
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(
                                function (Set $set): void {
                                    $set('ubicacion_destino_id', null);
                                    $set('usuario_recibe_id', null);
                                }
                            ),

                        /*
                         * DESTINO:
                         * UBICACIONES ACTIVAS EXCEPTO EL ORIGEN.
                         */
                        Select::make('ubicacion_destino_id')
                            ->label('Ubicación de destino')
                            ->options(
                                function (Get $get): array {

                                    $origen = $get(
                                        'ubicacion_origen_id'
                                    );

                                    if (! $origen) {
                                        return [];
                                    }

                                    return Ubicacione::query()
                                        ->where('activo', true)
                                        ->where('id', '!=', $origen)
                                        ->pluck('nombre', 'id')
                                        ->toArray();
                                }
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->live()
                            ->disabled(
                                fn (Get $get): bool =>
                                ! $get('ubicacion_origen_id')
                            )
                            ->afterStateUpdated(
                                fn (Set $set) =>
                                $set('usuario_recibe_id', null)
                            ),

                    ])
                    ->columns(2),

                Section::make('Usuario destinatario')
                    ->description(
                        'Solo aparecerán usuarios asignados a la ubicación de destino.'
                    )
                    ->schema([

                        Select::make('usuario_recibe_id')
                            ->label('Enviar transferencia a')
                            ->options(
                                function (Get $get): array {

                                    $destino = $get(
                                        'ubicacion_destino_id'
                                    );

                                    if (! $destino) {
                                        return [];
                                    }

                                    return User::query()
                                        ->whereHas(
                                            'ubicaciones',
                                            function ($query) use ($destino) {

                                                $query->where(
                                                    'ubicaciones.id',
                                                    $destino
                                                );
                                            }
                                        )
                                        ->whereKeyNot(
                                            auth()->id()
                                        )
                                        ->pluck('name', 'id')
                                        ->toArray();
                                }
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->disabled(
                                fn (Get $get): bool =>
                                ! $get('ubicacion_destino_id')
                            ),

                    ]),

                Section::make('Observaciones')
                    ->schema([

                        Textarea::make('observacion')
                            ->label('Observación')
                            ->rows(3)
                            ->columnSpanFull(),

                    ]),
            ]);
    }
}
