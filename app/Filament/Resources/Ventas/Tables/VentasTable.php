<?php

namespace App\Filament\Resources\Ventas\Tables;

use App\Models\Venta;
use App\Services\VentaService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class VentasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero')
                    ->label('N° Venta')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('ubicacion.nombre')
                    ->label('Tienda')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('cliente.nombre')
                    ->label('Cliente')
                    ->placeholder('Sin cliente')
                    ->searchable(),

                TextColumn::make('usuario.name')
                    ->label('Vendedor')
                    ->searchable(),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(
                        fn (string $state): string =>
                        match ($state) {
                            'pendiente' => 'Pendiente',
                            'reservada' => 'Reservada',
                            'vendida' => 'Vendida',
                            'cancelada' => 'Cancelada',
                            default => $state,
                        }
                    ),

                TextColumn::make('total')
                    ->label('Total')
                    ->money('BOB')
                    ->sortable(),

                TextColumn::make('fecha')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('fecha_venta')
                    ->label('Vendida el')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-')
                    ->toggleable(
                        isToggledHiddenByDefault: true
                    ),
            ])

            ->filters([
                SelectFilter::make('estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'reservada' => 'Reservada',
                        'vendida' => 'Vendida',
                        'cancelada' => 'Cancelada',
                    ]),

                SelectFilter::make('ubicacione_id')
                    ->label('Tienda')
                    ->relationship(
                        'ubicacion',
                        'nombre'
                    ),
            ])

            ->defaultSort('id', 'desc')

            ->recordActions([
                EditAction::make(),
                Action::make('reservar')
                    ->label('Reservar')
                    ->icon('heroicon-o-lock-closed')
                    ->color('warning')

                    ->requiresConfirmation()

                    ->modalHeading('Reservar productos')

                    ->modalDescription(
                        'Se reservará el stock de todos los productos de esta venta.'
                    )

                    ->modalSubmitActionLabel('Sí, reservar')

                    ->visible(
                        fn (Venta $record): bool =>
                            $record->estado === 'pendiente'
                    )

                    ->action(function (Venta $record): void {

                        VentaService::reservar($record);

                        Notification::make()
                            ->title('Venta reservada')
                            ->body(
                                'Los productos fueron reservados correctamente.'
                            )
                            ->success()
                            ->send();
                    }),
                Action::make('vender')
                    ->label('Confirmar venta')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')

                    ->requiresConfirmation()

                    ->modalHeading('Confirmar venta')

                    ->modalDescription(
                        'Se descontará el stock correspondiente a todos los productos de esta venta. Esta operación confirmará la venta.'
                    )

                    ->modalSubmitActionLabel('Sí, confirmar venta')

                    ->visible(
                        fn (Venta $record): bool =>
                        in_array(
                            $record->estado,
                            ['pendiente', 'reservada'],
                            true
                        )
                    )

                    ->action(function (Venta $record): void {

                        VentaService::vender($record);

                        Notification::make()
                            ->title('Venta confirmada')
                            ->body(
                                'La venta fue confirmada y el inventario fue actualizado correctamente.'
                            )
                            ->success()
                            ->send();
                    }),
                Action::make('cancelar')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')

                    ->requiresConfirmation()

                    ->modalHeading('Cancelar venta')

                    ->modalDescription(
                        '¿Está seguro de cancelar esta venta? Si existen productos reservados, serán liberados.'
                    )

                    ->modalSubmitActionLabel('Sí, cancelar')

                    ->visible(
                        fn (Venta $record): bool =>
                        in_array(
                            $record->estado,
                            ['pendiente', 'reservada'],
                            true
                        )
                    )

                    ->action(function (Venta $record): void {

                        VentaService::cancelar($record);

                        Notification::make()
                            ->title('Venta cancelada')
                            ->body(
                                'La venta fue cancelada correctamente.'
                            )
                            ->success()
                            ->send();
                    }),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
