<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatTiposAccionables extends Model
{
    protected $table = 'cat_tipos_accionables';

    protected $primaryKey = 'uuid_tipo_accionable';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['uuid_tipo_accionable', 'tipo_accionable'];

    public function resultadosAccionables(): HasMany
    {
        return $this->hasMany(TblResultadoAccionable::class, 'uuid_tipo_accionable', 'uuid_tipo_accionable');
    }
}
