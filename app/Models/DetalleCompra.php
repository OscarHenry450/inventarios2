<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
#[Fillable([
    'compra_id',
    'producto_id',
    'cantidad',
    'precio_compra',
    'subtotal',
])]
class DetalleCompra extends Model
{
    use HasFactory;

    protected $table = 'detalle_compras';

    protected function casts(): array
    {
        return [
            'cantidad' => 'integer',
            'precio_compra' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function compra()
    {
        return $this->belongsTo(Compra::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
