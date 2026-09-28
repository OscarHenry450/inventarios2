<?php

namespace App\Filament\Resources\Compras\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CompraInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información de la compra')
                    ->schema([

                        TextEntry::make('numero')
                            ->label('N° Compra'),

                        TextEntry::make('proveedor')
                            ->label('Proveedor'),

                        TextEntry::make('numero_documento')
                            ->label('Documento')
                            ->placeholder('Sin documento'),

                        TextEntry::make('fecha')
                            ->label('Fecha')
                            ->dateTime('d/m/Y H:i'),

                        TextEntry::make('ubicacion.nombre')
                            ->label('Ubicación destino'),

                        TextEntry::make('usuario.name')
                            ->label('Registrado por'),

                    ])
                    ->columns(2),

                Section::make('Estado y total')
                    ->schema([

                        TextEntry::make('estado')
                            ->label('Estado')
                            ->badge()
                            ->formatStateUsing(
                                fn (string $state): string => match ($state) {
                                    'pendiente' => 'Pendiente',
                                    'confirmada' => 'Confirmada',
                                    'cancelada' => 'Cancelada',
                                    default => ucfirst($state),
                                }
                            )
                            ->color(
                                fn (string $state): string => match ($state) {
                                    'pendiente' => 'warning',
                                    'confirmada' => 'success',
                                    'cancelada' => 'danger',
                                    default => 'gray',
                                }
                            ),

                        TextEntry::make('total')
                            ->label('Total de la compra')
                            ->money('BOB'),

                    ])
                    ->columns(2),

                Section::make('Observaciones')
                    ->schema([

                        TextEntry::make('observaciones')
                            ->label('Observaciones')
                            ->placeholder('Sin observaciones')
                            ->columnSpanFull(),

                    ]),

                Section::make('Información del registro')
                    ->schema([

                        TextEntry::make('created_at')
                            ->label('Fecha de creación')
                            ->dateTime('d/m/Y H:i'),

                        TextEntry::make('updated_at')
                            ->label('Última actualización')
                            ->dateTime('d/m/Y H:i'),

                    ])
                    ->columns(2)
                    ->collapsed(),

            ]);
    }
}
