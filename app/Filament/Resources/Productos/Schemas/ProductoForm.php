<?php

namespace App\Filament\Resources\Productos\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del producto')
                    ->description('Registra los datos principales del producto.')
                    ->schema([
                        TextInput::make('nombre')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('Nombre del producto'),
                        Select::make('marca_id') ->label('Marca')
                            ->relationship( name: 'marca', titleAttribute: 'nombre' )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),
                        TextInput::make('modelo')
                            ->label('Modelo')
                            ->maxLength(100)
                            ->placeholder('Ej: Air Max 2026'),
                        Toggle::make('estado')
                            ->label('Activo')
                            ->default(true)
                            ->required(),
                        Textarea::make('descripcion')
                            ->label('Descripción')
                            ->rows(4)
                            ->maxLength(1000)
                            ->columnSpanFull() ])
                    ->columns(2), Section::make('Imagen')
                    ->schema([ FileUpload::make('foto')
                        ->label('Imagen del producto') ->image()
                        ->disk('public')
                        ->directory('productos')
                        ->visibility('public')
                        ->imageEditor()
                        ->imagePreviewHeight('250')
                        ->maxSize(2048)
                        ->acceptedFileTypes([ 'image/jpeg', 'image/png', 'image/webp', ])
                        ->columnSpanFull(), ]),
            ]);
    }
}
