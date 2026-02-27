<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TblAlertasSeguimiento extends Model
{
    protected $table = 'tbl_alertas_seguimiento';

    protected $fillable = ['cve_empleado'];

    public function resultadosAccionables(): HasMany
    {
        return $this->hasMany(TblResultadoAccionable::class, 'id_alerta_seguimiento', 'id');
    }
}
