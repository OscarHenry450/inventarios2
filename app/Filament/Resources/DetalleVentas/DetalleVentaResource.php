<?php

namespace App\Filament\Resources\DetalleVentas;

use App\Filament\Resources\DetalleVentas\Pages\CreateDetalleVenta;
use App\Filament\Resources\DetalleVentas\Pages\EditDetalleVenta;
use App\Filament\Resources\DetalleVentas\Pages\ListDetalleVentas;
use App\Filament\Resources\DetalleVentas\Pages\ViewDetalleVenta;
use App\Filament\Resources\DetalleVentas\Schemas\DetalleVentaForm;
use App\Filament\Resources\DetalleVentas\Schemas\DetalleVentaInfolist;
use App\Filament\Resources\DetalleVentas\Tables\DetalleVentasTable;
use App\Models\DetalleVenta;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DetalleVentaResource extends Resource
{
    protected static ?string $model = DetalleVenta::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'cantidad';

    public static function form(Schema $schema): Schema
    {
        return DetalleVentaForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DetalleVentaInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DetalleVentasTable::configure($table);
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
            'index' => ListDetalleVentas::route('/'),
            'create' => CreateDetalleVenta::route('/create'),
            'view' => ViewDetalleVenta::route('/{record}'),
            'edit' => EditDetalleVenta::route('/{record}/edit'),
        ];
    }
}
