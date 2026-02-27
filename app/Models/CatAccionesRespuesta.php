<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatAccionesRespuesta extends Model
{
    protected $table = 'cat_acciones_respuesta';

    protected $fillable = ['descripcion'];

    public function resultadosAccionables(): HasMany
    {
        return $this->hasMany(TblResultadoAccionable::class, 'id_accion_respuesta', 'id');
    }
}
