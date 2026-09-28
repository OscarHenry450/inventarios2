<?php

namespace App\Filament\Resources\Transferencias\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TransferenciaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Información de la transferencia')
                    ->schema([

                        TextEntry::make('numero')
                            ->label('N.º Transferencia'),

                        TextEntry::make('estado')
                            ->label('Estado')
                            ->badge()
                            ->formatStateUsing(
                                fn (string $state): string => match ($state) {
                                    'pendiente' => 'Pendiente',
                                    'preparando' => 'Preparando',
                                    'en_transito' => 'En tránsito',
                                    'completada' => 'Completada',
                                    'cancelada' => 'Cancelada',
                                    default => $state,
                                }
                            )
                            ->color(
                                fn (string $state): string => match ($state) {
                                    'pendiente' => 'gray',
                                    'preparando' => 'warning',
                                    'en_transito' => 'info',
                                    'completada' => 'success',
                                    'cancelada' => 'danger',
                                    default => 'gray',
                                }
                            ),

                        TextEntry::make('ubicacionOrigen.nombre')
                            ->label('Ubicación origen'),

                        TextEntry::make('ubicacionDestino.nombre')
                            ->label('Ubicación destino'),

                    ])
                    ->columns(2),

                Section::make('Usuarios')
                    ->schema([

                        TextEntry::make('usuario.name')
                            ->label('Solicitado por')
                            ->placeholder('-'),

                        TextEntry::make('usuarioRecibe.name')
                            ->label('Recibido por')
                            ->placeholder('Aún no recibido'),

                    ])
                    ->columns(2),

                Section::make('Fechas')
                    ->schema([

                        TextEntry::make('fecha_solicitud')
                            ->label('Fecha solicitud')
                            ->dateTime('d/m/Y H:i'),

                        TextEntry::make('fecha_envio')
                            ->label('Fecha envío')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('Pendiente'),

                        TextEntry::make('fecha_recepcion')
                            ->label('Fecha recepción')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('Pendiente'),

                    ])
                    ->columns(3),

                Section::make('Observación')
                    ->schema([

                        TextEntry::make('observacion')
                            ->label('Observación')
                            ->placeholder('Sin observaciones')
                            ->columnSpanFull(),

                    ]),

                Section::make('Registro')
                    ->schema([

                        TextEntry::make('created_at')
                            ->label('Creado')
                            ->dateTime('d/m/Y H:i'),

                        TextEntry::make('updated_at')
                            ->label('Última modificación')
                            ->dateTime('d/m/Y H:i'),

                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }
}
