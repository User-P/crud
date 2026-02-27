<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatAcciones extends Model
{
    protected $table = 'cat_acciones';

    protected $primaryKey = 'uuid_accion';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['uuid_accion', 'descripcion'];

    public function resultadosAccionables(): HasMany
    {
        return $this->hasMany(TblResultadoAccionable::class, 'uuid_accion', 'uuid_accion');
    }
}
