<?php

namespace App\Filament\Resources\Cantidades;

use App\Filament\Resources\Cantidades\Pages\CreateCantidade;
use App\Filament\Resources\Cantidades\Pages\EditCantidade;
use App\Filament\Resources\Cantidades\Pages\ListCantidades;
use App\Filament\Resources\Cantidades\Schemas\CantidadeForm;
use App\Filament\Resources\Cantidades\Tables\CantidadesTable;
use App\Models\Cantidade;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CantidadeResource extends Resource
{
    protected static ?string $model = Cantidade::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'nombre';

    public static function form(Schema $schema): Schema
    {
        return CantidadeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CantidadesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCantidades::route('/'),
            'create' => CreateCantidade::route('/create'),
            'edit' => EditCantidade::route('/{record}/edit'),
        ];
    }
}
