<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
#[Fillable([
    'numero',
    'proveedor',
    'ubicacione_id',
    'user_id',
    'fecha',
    'numero_documento',
    'total',
    'estado',
    'observaciones',
])]
class Compra extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'fecha' => 'datetime',
            'total' => 'decimal:2',
        ];
    }

    public function ubicacion()
    {
        return $this->belongsTo(Ubicacione::class,'ubicacione_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleCompra::class);
    }
}
