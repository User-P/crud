<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatDirectivo extends Model
{
    protected $table = 'cat_directivos';

    protected $fillable = ['nm_directivo', 'siglas_directivo'];
}
