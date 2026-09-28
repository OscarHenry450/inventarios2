<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Attributes\Fillable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
#[Fillable([
    'nombre',
    'estado',
])]
class Marca extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'estado' => 'boolean',
        ];
    }

    public function productos()
    {
        return $this->hasMany(Producto::class);
    }
}
