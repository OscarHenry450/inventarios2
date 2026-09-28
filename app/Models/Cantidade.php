<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
#[Fillable([
    'nombre',
    'cantidad_unidades',
    'activo',
])]
class Cantidade extends Model
{
    use HasFactory;

    protected $table = 'cantidades';

    protected function casts(): array
    {
        return [
            'cantidad_unidades' => 'integer',
            'activo' => 'boolean',
        ];
    }

    public function productoPrecios()
    {
        return $this->hasMany(
            ProductoPrecio::class,
            'cantidade_id'
        );
    }
}
