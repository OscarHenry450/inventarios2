<?php

namespace App\Filament\Resources\Transferencias;

use App\Filament\Resources\Transferencias\Pages\CreateTransferencia;
use App\Filament\Resources\Transferencias\Pages\EditTransferencia;
use App\Filament\Resources\Transferencias\Pages\ListTransferencias;
use App\Filament\Resources\Transferencias\Pages\ViewTransferencia;
use App\Filament\Resources\Transferencias\RelationManagers\DetallesRelationManager;
use App\Filament\Resources\Transferencias\Schemas\TransferenciaForm;
use App\Filament\Resources\Transferencias\Tables\TransferenciasTable;
use App\Models\Transferencia;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
class TransferenciaResource extends Resource
{
    protected static ?string $model = Transferencia::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'numero';

    public static function form(Schema $schema): Schema
    {
        return TransferenciaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TransferenciasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            DetallesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTransferencias::route('/'),
            'create' => CreateTransferencia::route('/create'),
            'view' => ViewTransferencia::route('/{record}'),
            'edit' => EditTransferencia::route('/{record}/edit'),
        ];
    }
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where(function (Builder $query): void {

                $query
                    ->where('user_id', auth()->id())
                    ->orWhere(
                        'usuario_recibe_id',
                        auth()->id()
                    );
            });
    }
}
