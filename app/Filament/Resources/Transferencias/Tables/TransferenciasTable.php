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
                    ->searchable(),

                TextColumn::make('ubicacionDestino.nombre')
                    ->label('Destino')
                    ->searchable(),

                TextColumn::make('usuario.name')
                    ->label('Remitente')
                    ->searchable(),

                TextColumn::make('usuarioRecibe.name')
                    ->label('Destinatario')
                    ->searchable(),

                /*
                 * Mostramos un estado visual.
                 *
                 * No estamos modificando el enum de la BD.
                 */
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(
                        function (
                            string $state,
                                   $record
                        ): string {

                            if ($state === 'completada') {
                                return 'Completada';
                            }

                            if ($state === 'cancelada') {
                                return 'Cancelada';
                            }

                            if (
                                $state === 'pendiente'
                                && $record->fecha_envio !== null
                            ) {
                                return 'Enviada';
                            }

                            return 'En preparación';
                        }
                    )
                    ->color(
                        function (
                            string $state,
                                   $record
                        ): string {

                            if ($state === 'completada') {
                                return 'success';
                            }

                            if ($state === 'cancelada') {
                                return 'danger';
                            }

                            if (
                                $state === 'pendiente'
                                && $record->fecha_envio !== null
                            ) {
                                return 'info';
                            }

                            return 'warning';
                        }
                    ),

                TextColumn::make('fecha_solicitud')
                    ->label('Solicitud')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('fecha_envio')
                    ->label('Envío')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('No enviada')
                    ->toggleable(),

                TextColumn::make('fecha_recepcion')
                    ->label('Recepción')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('No recibida')
                    ->toggleable(),

            ])

            ->filters([

                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'completada' => 'Completada',
                        'cancelada' => 'Cancelada',
                    ]),

            ])

            ->recordActions([

                ViewAction::make()
                    ->label('Ver'),

                EditAction::make()
                    ->label('Editar')
                    ->visible(
                        fn ($record): bool =>
                            $record->estado === 'pendiente'
                            && $record->fecha_envio === null
                            && (int) $record->user_id
                            === (int) auth()->id()
                    ),

            ])

            ->defaultSort('id', 'desc');
    }
}
