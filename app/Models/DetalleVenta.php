<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
#[Fillable([
    'venta_id',
    'producto_id',
    'producto_precio_id',
    'cantidad',
    'unidades_totales',
    'precio_unitario',
    'descuento',
    'subtotal',
])]
class DetalleVenta extends Model
{
    use HasFactory;

    protected $table = 'detalle_ventas';

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:2',
            'unidades_totales' => 'decimal:2',
            'precio_unitario' => 'decimal:2',
            'descuento' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function venta()
    {
        return $this->belongsTo(
            Venta::class,
            'venta_id'
        );
    }

    public function producto()
    {
        return $this->belongsTo(
            Producto::class,
            'producto_id'
        );
    }

    public function productoPrecio()
    {
        return $this->belongsTo(
            ProductoPrecio::class,
            'producto_precio_id'
        );
    }
}
