<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
#[Fillable([
    'transferencia_id',
    'producto_id',
    'cantidad_solicitada',
    'cantidad_enviada',
    'cantidad_recibida',
])]
class DetalleTransferencia extends Model
{
    use HasFactory;

    protected $table = 'detalle_transferencias';

    protected function casts(): array
    {
        return [
            'cantidad_solicitada' => 'decimal:2',
            'cantidad_enviada' => 'decimal:2',
            'cantidad_recibida' => 'decimal:2',
        ];
    }

    public function transferencia()
    {
        return $this->belongsTo(Transferencia::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
