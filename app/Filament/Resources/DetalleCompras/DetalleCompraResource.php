<?php

namespace App\Filament\Resources\DetalleCompras;

use App\Filament\Resources\DetalleCompras\Pages\CreateDetalleCompra;
use App\Filament\Resources\DetalleCompras\Pages\EditDetalleCompra;
use App\Filament\Resources\DetalleCompras\Pages\ListDetalleCompras;
use App\Filament\Resources\DetalleCompras\Pages\ViewDetalleCompra;
use App\Filament\Resources\DetalleCompras\Schemas\DetalleCompraForm;
use App\Filament\Resources\DetalleCompras\Schemas\DetalleCompraInfolist;
use App\Filament\Resources\DetalleCompras\Tables\DetalleComprasTable;
use App\Models\DetalleCompra;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DetalleCompraResource extends Resource
{
    protected static ?string $model = DetalleCompra::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'cantidad';

    public static function form(Schema $schema): Schema
    {
        return DetalleCompraForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DetalleCompraInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DetalleComprasTable::configure($table);
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
            'index' => ListDetalleCompras::route('/'),
            'create' => CreateDetalleCompra::route('/create'),
            'view' => ViewDetalleCompra::route('/{record}'),
            'edit' => EditDetalleCompra::route('/{record}/edit'),
        ];
    }
}
