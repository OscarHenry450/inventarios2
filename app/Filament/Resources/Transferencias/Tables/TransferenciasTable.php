<?php

namespace App\Filament\Resources\Transferencias\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TransferenciasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero')
                    ->label('N.º Transferencia')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('ubicacionOrigen.nombre')
                    ->label('Origen')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('ubicacionDestino.nombre')
                    ->label('Destino')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pendiente' => 'Pendiente',
                        'preparando' => 'Preparando',
                        'en_transito' => 'En tránsito',
                        'completada' => 'Completada',
                        'cancelada' => 'Cancelada',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pendiente' => 'gray',
                        'preparando' => 'warning',
                        'en_transito' => 'info',
                        'completada' => 'success',
                        'cancelada' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('fecha_solicitud')
                    ->label('Fecha solicitud')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('fecha_envio')
                    ->label('Fecha envío')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('fecha_recepcion')
                    ->label('Fecha recepción')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('usuario.name')
                    ->label('Solicitado por')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('usuarioRecibe.name')
                    ->label('Recibido por')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([

                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'preparando' => 'Preparando',
                        'en_transito' => 'En tránsito',
                        'completada' => 'Completada',
                        'cancelada' => 'Cancelada',
                    ]),

                SelectFilter::make('ubicacion_origen_id')
                    ->label('Origen')
                    ->relationship(
                        'ubicacionOrigen',
                        'nombre'
                    )
                    ->searchable()
                    ->preload(),

                SelectFilter::make('ubicacion_destino_id')
                    ->label('Destino')
                    ->relationship(
                        'ubicacionDestino',
                        'nombre'
                    )
                    ->searchable()
                    ->preload(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Ver'),

                EditAction::make()
                    ->label('Editar')
                    ->visible(
                        fn ($record): bool =>
                            $record->estado === 'pendiente'
                    ),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
        ->defaultSort('id', 'desc');
    }
}
