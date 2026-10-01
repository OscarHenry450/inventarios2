<?php

namespace App\Filament\Resources\Transferencias\Pages;

use App\Filament\Resources\Transferencias\TransferenciaResource;
use App\Models\Inventario;
use App\Models\Transferencia;
use App\Models\User;
use App\Services\TransferenciaService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Exception;
use Illuminate\Support\Facades\DB;

class ViewTransferencia extends ViewRecord
{
    protected static string $resource = TransferenciaResource::class;

    protected function getHeaderActions(): array
    {
        return [

            /*
             * EDITAR
             */
            EditAction::make()
                ->visible(
                    fn (): bool =>
                    $this->puedeEditar()
                ),

            /*
             * ENVIAR AL DESTINATARIO
             */
            Action::make('enviar')
                ->label('Enviar solicitud')
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading(
                    'Enviar transferencia'
                )
                ->modalDescription(
                    'Los productos serán descontados del inventario de origen y la transferencia será enviada al destinatario.'
                )
                ->modalSubmitActionLabel(
                    'Sí, enviar'
                )
                ->visible(
                    fn (): bool =>
                    $this->puedeEditar()
                )
                ->action(function (): void {

                    try {

                        app(TransferenciaService::class)
                            ->enviar($this->record);

                        $this->record->refresh();

                        Notification::make()
                            ->title(
                                'Transferencia enviada'
                            )
                            ->body(
                                'El inventario fue actualizado y el destinatario recibió una notificación.'
                            )
                            ->success()
                            ->send();

                    } catch (Exception $e) {

                        Notification::make()
                            ->title(
                                'No se pudo enviar'
                            )
                            ->body(
                                $e->getMessage()
                            )
                            ->danger()
                            ->send();
                    }
                }),
            Action::make('recibir')
                ->label('Confirmar recepción')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading(
                    'Confirmar recepción'
                )
                ->modalDescription(
                    'Confirma únicamente si los productos llegaron a la ubicación de destino.'
                )
                ->modalSubmitActionLabel(
                    'Sí, recibí los productos'
                )
                ->visible(
                    fn (): bool =>
                    $this->puedeRecibir()
                )
                ->action(function (): void {

                    try {

                        app(TransferenciaService::class)
                            ->recibir($this->record);

                        $this->record->refresh();

                        Notification::make()
                            ->title(
                                'Transferencia recibida'
                            )
                            ->body(
                                'Los productos fueron agregados al inventario de destino.'
                            )
                            ->success()
                            ->send();

                    } catch (Exception $e) {

                        Notification::make()
                            ->title(
                                'No se pudo recibir la transferencia'
                            )
                            ->body(
                                $e->getMessage()
                            )
                            ->danger()
                            ->send();
                    }
                }),
        ];

    }
    public function recibir(Transferencia $transferencia): void
    {
        $remitente = null;

        DB::transaction(function () use (
            $transferencia,
            &$remitente
        ): void {

            $transferencia = Transferencia::query()
                ->with([
                    'detalles.producto',
                    'ubicacionDestino',
                ])
                ->lockForUpdate()
                ->findOrFail($transferencia->id);

            if ($transferencia->estado !== 'pendiente') {
                throw new Exception(
                    'Esta transferencia ya fue procesada.'
                );
            }

            if ($transferencia->fecha_envio === null) {
                throw new Exception(
                    'Esta transferencia todavía no fue enviada.'
                );
            }

            /*
             * Solo el destinatario puede recibir.
             */
            if (
                (int) $transferencia->usuario_recibe_id
                !== (int) auth()->id()
            ) {
                throw new Exception(
                    'No eres el destinatario de esta transferencia.'
                );
            }

            /*
             * El destinatario debe tener asignada
             * la ubicación destino.
             */
            $tieneDestino = auth()->user()
                ->ubicaciones()
                ->where(
                    'ubicaciones.id',
                    $transferencia->ubicacion_destino_id
                )
                ->exists();

            if (! $tieneDestino) {
                throw new Exception(
                    'No tienes acceso a la ubicación de destino.'
                );
            }

            if ($transferencia->detalles->isEmpty()) {
                throw new Exception(
                    'La transferencia no contiene productos.'
                );
            }

            /*
             * Validar productos enviados.
             */
            foreach ($transferencia->detalles as $detalle) {

                if (
                    $detalle->cantidad_enviada === null
                    || (float) $detalle->cantidad_enviada <= 0
                ) {
                    throw new Exception(
                        "El producto {$detalle->producto->nombre} no tiene una cantidad enviada válida."
                    );
                }
            }

            /*
             * Agregar productos al inventario destino.
             */
            foreach ($transferencia->detalles as $detalle) {

                $cantidad =
                    (float) $detalle->cantidad_enviada;

                $inventarioDestino = Inventario::query()
                    ->where(
                        'producto_id',
                        $detalle->producto_id
                    )
                    ->where(
                        'ubicacione_id',
                        $transferencia->ubicacion_destino_id
                    )
                    ->lockForUpdate()
                    ->first();

                /*
                 * Si no existe inventario para ese producto
                 * en el destino, lo creamos.
                 */
                if (! $inventarioDestino) {

                    $inventarioDestino = Inventario::create([
                        'producto_id' =>
                            $detalle->producto_id,

                        'ubicacione_id' =>
                            $transferencia->ubicacion_destino_id,

                        'stock' => 0,

                        'stock_reservado' => 0,

                        'stock_minimo' => 0,
                    ]);
                }

                /*
                 * Aumentar stock en destino.
                 */
                $inventarioDestino->increment(
                    'stock',
                    $cantidad
                );

                /*
                 * Registrar cantidad recibida.
                 */
                $detalle->update([
                    'cantidad_recibida' => $cantidad,
                ]);
            }

            /*
             * Completar transferencia.
             */
            $transferencia->update([
                'estado' => 'completada',
                'fecha_recepcion' => now(),
            ]);

            /*
             * Obtener usuario que creó la transferencia.
             */
            $remitente = User::find(
                $transferencia->user_id
            );
        });

        /*
         * IMPORTANTE:
         * La notificación se manda después de que
         * la transacción terminó correctamente.
         */
        if ($remitente) {

            Notification::make()
                ->title('Transferencia recibida')
                ->body(
                    'La transferencia N.º '
                    . $transferencia->numero
                    . ' fue recibida correctamente.'
                )
                ->success()
                ->sendToDatabase($remitente);
        }
    }
    private function puedeEditar(): bool
    {
        return
            $this->record->estado === 'pendiente'
            && $this->record->fecha_envio === null
            && (int) $this->record->user_id
            === (int) auth()->id();
    }
    private function puedeRecibir(): bool
    {
        return
            $this->record->estado === 'pendiente'
            && $this->record->fecha_envio !== null
            && (int) $this->record->usuario_recibe_id
            === (int) auth()->id();
    }
}
