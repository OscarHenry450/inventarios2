<?php

namespace App\Filament\Resources\Ubicaciones;

use App\Filament\Resources\Ubicaciones\Pages\CreateUbicacione;
use App\Filament\Resources\Ubicaciones\Pages\EditUbicacione;
use App\Filament\Resources\Ubicaciones\Pages\ListUbicaciones;
use App\Filament\Resources\Ubicaciones\Pages\ViewUbicacione;
use App\Filament\Resources\Ubicaciones\Schemas\UbicacioneForm;
use App\Filament\Resources\Ubicaciones\Schemas\UbicacioneInfolist;
use App\Filament\Resources\Ubicaciones\Tables\UbicacionesTable;
use App\Models\Ubicacione;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UbicacioneResource extends Resource
{
    protected static ?string $model = Ubicacione::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'nombre';

    public static function form(Schema $schema): Schema
    {
        return UbicacioneForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UbicacioneInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UbicacionesTable::configure($table);
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
            'index' => ListUbicaciones::route('/'),
            'create' => CreateUbicacione::route('/create'),
            'view' => ViewUbicacione::route('/{record}'),
            'edit' => EditUbicacione::route('/{record}/edit'),
        ];
    }
}
