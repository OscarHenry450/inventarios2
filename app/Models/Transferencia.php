<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
#[Fillable([
    'numero',
    'user_id',
    'ubicacion_origen_id',
    'ubicacion_destino_id',
    'usuario_id',
    'usuario_recibe_id',
    'estado',
    'fecha_solicitud',
    'fecha_envio',
    'fecha_recepcion',
    'observacion',
])]
class Transferencia extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'fecha_solicitud' => 'datetime',
            'fecha_envio' => 'datetime',
            'fecha_recepcion' => 'datetime',
        ];
    }

    public function ubicacionOrigen()
    {
        return $this->belongsTo(
            Ubicacione::class,
            'ubicacion_origen_id'
        );
    }

    public function ubicacionDestino()
    {
        return $this->belongsTo(
            Ubicacione::class,
            'ubicacion_destino_id'
        );
    }

    public function usuario()
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }

    public function usuarioRecibe()
    {
        return $this->belongsTo(
            User::class,
            'usuario_recibe_id'
        );
    }

    public function detalles()
    {
        return $this->hasMany(
            DetalleTransferencia::class
        );
    }
}
