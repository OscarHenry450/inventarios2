<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
#[Fillable([
    'producto_id',
    'ubicacione_id',
    'stock',
    'stock_reservado',
    'stock_minimo',
])]
class Inventario extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'stock' => 'decimal:2',
            'stock_reservado' => 'decimal:2',
            'stock_minimo' => 'decimal:2',
        ];
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function ubicacion()
    {
        return $this->belongsTo(Ubicacione::class, 'ubicacione_id');
    }

    public function getStockDisponibleAttribute(): float
    {
        return (float) $this->stock
            - (float) $this->stock_reservado;
    }
}
