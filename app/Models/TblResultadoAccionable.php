<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TblResultadoAccionable extends Model
{
    protected $table = 'tbl_resultado_accionable';

    protected $fillable = [
        'uuid_tipo_accionable',
        'uuid_accion',
        'id_accion_respuesta',
        'id_alerta_seguimiento',
        'fch_solicitud',
    ];

    protected $casts = [
        'fch_solicitud' => 'date',
    ];

    public function tipoAccionable(): BelongsTo
    {
        return $this->belongsTo(CatTiposAccionables::class, 'uuid_tipo_accionable', 'uuid_tipo_accionable');
    }

    public function accion(): BelongsTo
    {
        return $this->belongsTo(CatAcciones::class, 'uuid_accion', 'uuid_accion');
    }

    public function accionRespuesta(): BelongsTo
    {
        return $this->belongsTo(CatAccionesRespuesta::class, 'id_accion_respuesta', 'id');
    }

    public function alertaSeguimiento(): BelongsTo
    {
        return $this->belongsTo(TblAlertasSeguimiento::class, 'id_alerta_seguimiento', 'id');
    }
}
