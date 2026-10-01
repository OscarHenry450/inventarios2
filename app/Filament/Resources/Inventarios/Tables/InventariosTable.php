<?php

namespace App\Filament\Resources\Inventarios\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InventariosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                /*
                 * PRODUCTO
                 */
                TextColumn::make('producto.nombre')
                    ->label('Producto')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                /*
                 * MARCA
                 */
                TextColumn::make('producto.marca.nombre')
                    ->label('Marca')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Sin marca'),
                TextColumn::make('producto.modelo')
                    ->label('Modelo')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Sin Modelo'),

                /*
                 * UBICACIÓN
                 */
                TextColumn::make('ubicacion.nombre')
                    ->label('Ubicación')
                    ->searchable()
                    ->sortable()
                    ->description(
                        fn ($record): string =>
                            $record->ubicacione?->direccion
                            ?? 'Sin dirección'
                    ),

                /*
                 * TIPO DE UBICACIÓN
                 */
                TextColumn::make('ubicacion.tipo')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(
                        fn (?string $state): string =>
                        match ($state) {
                            'almacen' => 'Almacén',
                            'tienda' => 'Tienda',
                            default => $state ?? 'Sin tipo',
                        }
                    )
                    ->color(
                        fn (?string $state): string =>
                        match ($state) {
                            'almacen' => 'info',
                            'tienda' => 'success',
                            default => 'gray',
                        }
                    ),

                /*
                 * STOCK FÍSICO
                 */
                TextColumn::make('stock')
                    ->label('Stock')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->alignEnd(),

                /*
                 * STOCK RESERVADO
                 */
                TextColumn::make('stock_reservado')
                    ->label('Reservado')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->alignEnd()
                    ->color(
                        fn ($state): string =>
                        (float) $state > 0
                            ? 'warning'
                            : 'gray'
                    ),

                /*
                 * STOCK DISPONIBLE
                 *
                 * stock - stock_reservado
                 */
                TextColumn::make('stock_disponible')
                    ->label('Disponible')
                    ->state(
                        fn ($record): float =>
                        max(
                            0,
                            (float) $record->stock
                            - (float) $record->stock_reservado
                        )
                    )
                    ->numeric(decimalPlaces: 2)
                    ->weight('bold')
                    ->color(
                        fn ($record): string =>
                        (
                            (float) $record->stock
                            - (float) $record->stock_reservado
                        ) <= (float) $record->stock_minimo
                            ? 'danger'
                            : 'success'
                    )
                    ->alignEnd(),

                /*
                 * STOCK MÍNIMO
                 */
                TextColumn::make('stock_minimo')
                    ->label('Mínimo')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(),

                /*
                 * ESTADO DEL INVENTARIO
                 */
                TextColumn::make('estado_stock')
                    ->label('Estado')
                    ->badge()
                    ->state(function ($record): string {

                        $disponible =
                            (float) $record->stock
                            - (float) $record->stock_reservado;

                        if ($disponible <= 0) {
                            return 'Sin stock';
                        }

                        if (
                            $disponible
                            <= (float) $record->stock_minimo
                        ) {
                            return 'Stock bajo';
                        }

                        return 'Disponible';
                    })
                    ->color(
                        fn (string $state): string =>
                        match ($state) {
                            'Sin stock' => 'danger',
                            'Stock bajo' => 'warning',
                            'Disponible' => 'success',
                            default => 'gray',
                        }
                    ),

                /*
                 * FECHA ACTUALIZACIÓN
                 */
                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(
                        isToggledHiddenByDefault: true
                    ),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(
                        isToggledHiddenByDefault: true
                    ),
            ])

            ->filters([
                //
            ])

            ->recordActions([

                ViewAction::make()
                    ->label('Ver'),

                EditAction::make()
                    ->label('Editar'),

            ])

            ->toolbarActions([

                BulkActionGroup::make([

                    DeleteBulkAction::make(),

                ]),

            ])

            ->defaultSort('updated_at', 'desc');
    }
}
