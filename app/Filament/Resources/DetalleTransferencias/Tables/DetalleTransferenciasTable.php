<?php

namespace App\Filament\Resources\DetalleTransferencias\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DetalleTransferenciasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('transferencia_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('producto_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('cantidad_solicitada')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('cantidad_enviada')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('cantidad_recibida')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
