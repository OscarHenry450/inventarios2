<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
#[Fillable(['nombre','apellido','documento','telefono','email','activo'])]
class Cliente extends Model
{
    use HasFactory;
    public function ventas()
    {
        return $this->hasMany(
            Venta::class,
            'cliente_id'
        );
    }
}
