<?php

namespace App\Filament\Resources\Productos\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PreciosRelationManager extends RelationManager
{
    protected static string $relationship = 'precios';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                Select::make('cantidade_id')
                    ->label('Presentación')
                    ->relationship(
                        name: 'cantidad',
                        titleAttribute: 'nombre',
                        modifyQueryUsing: fn (Builder $query): Builder =>
                        $query->where('activo', true)
                    )
                    ->searchable()
                    ->preload()
                    ->required()
                    ->native(false),

                TextInput::make('precio_unitario')
                    ->label('Precio de venta')
                    ->prefix('Bs')
                    ->numeric()
                    ->minValue(0.01)
                    ->required(),

                Toggle::make('activo')
                    ->label('Activo')
                    ->default(true),

            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')

            ->columns([

                TextColumn::make('cantidad.nombre')
                    ->label('Presentación')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('cantidad.cantidad_unidades')
                    ->label('Unidades')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('precio_venta')
                    ->label('Precio')
                    ->money('BOB')
                    ->sortable(),

                IconColumn::make('activo')
                    ->label('Activo')
                    ->boolean(),

            ])

            ->headerActions([

                CreateAction::make()
                    ->label('Agregar precio'),

            ])

            ->recordActions([

                EditAction::make(),

                DeleteAction::make(),

            ]);
    }
}
