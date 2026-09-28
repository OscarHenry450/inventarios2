<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'marca_id',
    'codigo',
    'nombre',
    'descripcion',
    'modelo',
    'foto',
    'estado',
])]
class Producto extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'estado' => 'boolean',
        ];
    }

    public function marca()
    {
        return $this->belongsTo(Marca::class);
    }

    public function precios()
    {
        return $this->hasMany(ProductoPrecio::class);
    }

    public function inventarios()
    {
        return $this->hasMany(Inventario::class);
    }
    public function detalleTransferencias()
    {
        return $this->hasMany(DetalleTransferencia::class);
    }
    public function detalleVentas()
    {
        return $this->hasMany(
            DetalleVenta::class,
            'producto_id'
        );
    }
}
