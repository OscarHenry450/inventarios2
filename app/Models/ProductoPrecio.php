<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
#[Fillable(['producto_id',
    'cantidade_id',
    'precio_unitario',
    'activo'])]
class ProductoPrecio extends Model
{
    use HasFactory;
    protected function casts(): array
    {
        return [
            'precio_venta' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function cantidad()
    {
        return $this->belongsTo(Cantidade::class, 'cantidade_id');
    }
    public function detalleVentas()
    {
        return $this->hasMany(
            DetalleVenta::class,
            'producto_precio_id'
        );
    }
}
