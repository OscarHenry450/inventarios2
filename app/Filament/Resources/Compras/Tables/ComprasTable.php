<?php

namespace App\Filament\Resources\Compras\Tables;

use App\Models\Compra;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ComprasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero')
                    ->label('N° Compra')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('proveedor')
                    ->label('Proveedor')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('ubicacion.nombre')
                    ->label('Destino')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('fecha')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('total')
                    ->label('Total')
                    ->money('BOB')
                    ->sortable(),

                TextColumn::make('estado')
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
                    )
                    ->sortable(),

                TextColumn::make('usuario.name')
                    ->label('Registrado por')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('numero_documento')
                    ->label('Documento')
                    ->placeholder('Sin documento')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'confirmada' => 'Confirmada',
                        'cancelada' => 'Cancelada',
                    ])
                    ->native(false),

                SelectFilter::make('ubicacion_id')
                    ->label('Ubicación destino')
                    ->relationship('ubicacion', 'nombre')
                    ->searchable()
                    ->preload()
                    ->native(false),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()->visible(
                    fn ($record): bool =>
                        $record->estado === 'pendiente'
                ),
                DeleteAction::make()
                    ->visible(
                        fn (Compra $record): bool =>
                            $record->estado === 'pendiente'
                    ),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
