<?php

namespace App\Filament\Resources\Compras\Pages;

use App\Filament\Resources\Compras\CompraResource;
use App\Services\CompraService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Throwable;
class ViewCompra extends ViewRecord
{
    protected static string $resource = CompraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            Action::make('confirmar')
                ->label('Confirmar compra')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->modalHeading('Confirmar compra')
                ->modalDescription(
                    'Al confirmar la compra, los productos serán ingresados al inventario.'
                )
                ->modalSubmitActionLabel('Sí, confirmar')
                ->visible(
                    fn (): bool =>
                        $this->record->estado === 'pendiente'
                )
                ->action(function (): void {
                    try {
                        app(CompraService::class)
                            ->confirmar($this->record);

                        $this->record->refresh();

                        Notification::make()
                            ->title('Compra confirmada')
                            ->body(
                                'El inventario fue actualizado correctamente.'
                            )
                            ->success()
                            ->send();

                    } catch (Throwable $e) {

                        Notification::make()
                            ->title('No se pudo confirmar la compra')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Action::make('cancelar')
                ->label('Cancelar compra')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Cancelar compra')
                ->modalDescription(
                    'Esta acción cancelará la compra. No se modificará el inventario.'
                )
                ->modalSubmitActionLabel('Sí, cancelar')
                ->visible(
                    fn (): bool =>
                        $this->record->estado === 'pendiente'
                )
                ->action(function (): void {
                    try {
                        app(CompraService::class)
                            ->cancelar($this->record);

                        $this->record->refresh();

                        Notification::make()
                            ->title('Compra cancelada')
                            ->success()
                            ->send();

                    } catch (Throwable $e) {
                        Notification::make()
                            ->title('No se pudo cancelar')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
