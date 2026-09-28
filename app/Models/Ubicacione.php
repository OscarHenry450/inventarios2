<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
#[Fillable([
    'nombre',
    'direccion',
    'tipo',
    'activo',
])]
class Ubicacione extends Model
{
    use HasFactory;

    protected $table = 'ubicaciones';

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function inventarios()
    {
        return $this->hasMany(Inventario::class);
    }

    public function transferenciasOrigen()
    {
        return $this->hasMany(
            Transferencia::class,
            'ubicacion_origen_id'
        );
    }

    public function transferenciasDestino()
    {
        return $this->hasMany(
            Transferencia::class,
            'ubicacion_destino_id'
        );
    }
    public function ventas()
    {
        return $this->hasMany(
            Venta::class,
            'ubicacione_id'
        );
    }
    public function usuarios()
    {
        return $this->belongsToMany(
            User::class,
            'ubicacione_users',
            'ubicacione_id',
            'user_id'
        )->withTimestamps();
    }
}
