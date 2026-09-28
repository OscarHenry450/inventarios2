<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
#[Fillable([
    'numero',
    'ubicacione_id',
    'cliente_id',
    'user_id',
    'estado',
    'fecha',
    'fecha_reserva',
    'fecha_vencimiento_reserva',
    'fecha_venta',
    'subtotal',
    'descuento',
    'total',
    'observacion',
])]
class Venta extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'fecha' => 'datetime',
            'fecha_reserva' => 'datetime',
            'fecha_vencimiento_reserva' => 'datetime',
            'fecha_venta' => 'datetime',

            'subtotal' => 'decimal:2',
            'descuento' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function ubicacion()
    {
        return $this->belongsTo(
            Ubicacione::class,
            'ubicacione_id'
        );
    }

    public function cliente()
    {
        return $this->belongsTo(
            Cliente::class,
            'cliente_id'
        );
    }

    public function usuario()
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }

    public function detalles()
    {
        return $this->hasMany(
            DetalleVenta::class,
            'venta_id'
        );
    }
}
