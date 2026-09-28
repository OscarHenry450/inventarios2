<?php

namespace App\Filament\Resources\Transferencias\Pages;

use App\Filament\Resources\Transferencias\TransferenciaResource;
use App\Services\TransferenciaService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Exception;

class ViewTransferencia extends ViewRecord
{
    protected static string $resource = TransferenciaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            Action::make('preparar')
                ->label('Preparar')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Preparar transferencia')
                ->modalDescription(
                    'Se reservará el stock de los productos en la ubicación de origen.'
                )
                ->modalSubmitActionLabel('Sí, preparar')
                ->visible(
                    fn (): bool =>
                        $this->record->estado === 'pendiente'
                )
                ->action(function (): void {

                    try {

                        app(TransferenciaService::class)
                            ->preparar($this->record);

                        $this->record->refresh();

                        Notification::make()
                            ->title('Transferencia en preparación')
                            ->body(
                                'El stock fue reservado correctamente.'
                            )
                            ->success()
                            ->send();

                    } catch (Exception $e) {

                        Notification::make()
                            ->title('No se pudo preparar')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Action::make('enviar')
                ->label('Enviar')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Enviar transferencia')
                ->modalDescription(
                    'El stock será descontado físicamente de la ubicación de origen.'
                )
                ->modalSubmitActionLabel('Sí, enviar')
                ->visible(
                    fn (): bool =>
                        $this->record->estado === 'preparando'
                )
                ->action(function (): void {

                    try {

                        app(TransferenciaService::class)
                            ->enviar($this->record);

                        $this->record->refresh();

                        Notification::make()
                            ->title('Transferencia enviada')
                            ->body(
                                'Los productos ahora están en tránsito.'
                            )
                            ->success()
                            ->send();

                    } catch (Exception $e) {

                        Notification::make()
                            ->title('No se pudo enviar')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Action::make('recibir')
                ->label('Recibir')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Recibir transferencia')
                ->modalDescription(
                    'Los productos serán ingresados al inventario de la ubicación destino.'
                )
                ->modalSubmitActionLabel('Sí, recibir')
                ->visible(
                    fn (): bool =>
                        $this->record->estado === 'en_transito'
                )
                ->action(function (): void {

                    try {

                        app(TransferenciaService::class)
                            ->completar($this->record);

                        $this->record->refresh();

                        Notification::make()
                            ->title('Transferencia completada')
                            ->body(
                                'El inventario de destino fue actualizado correctamente.'
                            )
                            ->success()
                            ->send();

                    } catch (Exception $e) {

                        Notification::make()
                            ->title('No se pudo completar')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Action::make('cancelar')
                ->label('Cancelar')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Cancelar transferencia')
                ->modalDescription(
                    '¿Seguro que deseas cancelar esta transferencia?'
                )
                ->modalSubmitActionLabel('Sí, cancelar')
                ->visible(
                    fn (): bool =>
                    in_array(
                        $this->record->estado,
                        [
                            'pendiente',
                            'preparando',
                        ],
                        true
                    )
                )
                ->action(function (): void {

                    try {

                        app(TransferenciaService::class)
                            ->cancelar($this->record);

                        $this->record->refresh();

                        Notification::make()
                            ->title('Transferencia cancelada')
                            ->success()
                            ->send();

                    } catch (Exception $e) {

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
