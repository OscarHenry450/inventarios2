<?php

namespace App\Filament\Resources\DetalleTransferencias;

use App\Filament\Resources\DetalleTransferencias\Pages\CreateDetalleTransferencia;
use App\Filament\Resources\DetalleTransferencias\Pages\EditDetalleTransferencia;
use App\Filament\Resources\DetalleTransferencias\Pages\ListDetalleTransferencias;
use App\Filament\Resources\DetalleTransferencias\Pages\ViewDetalleTransferencia;
use App\Filament\Resources\DetalleTransferencias\Schemas\DetalleTransferenciaForm;
use App\Filament\Resources\DetalleTransferencias\Schemas\DetalleTransferenciaInfolist;
use App\Filament\Resources\DetalleTransferencias\Tables\DetalleTransferenciasTable;
use App\Models\DetalleTransferencia;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DetalleTransferenciaResource extends Resource
{
    protected static ?string $model = DetalleTransferencia::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'cantidad_solicitada';

    public static function form(Schema $schema): Schema
    {
        return DetalleTransferenciaForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DetalleTransferenciaInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DetalleTransferenciasTable::configure($table);
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
            'index' => ListDetalleTransferencias::route('/'),
            'create' => CreateDetalleTransferencia::route('/create'),
            'view' => ViewDetalleTransferencia::route('/{record}'),
            'edit' => EditDetalleTransferencia::route('/{record}/edit'),
        ];
    }
}
