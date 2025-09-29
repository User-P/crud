<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
  use HasFactory;

  protected $fillable = [
    'name',
    'code',
    'capital',
    'population',
    'region',
    'subregion',
    'flag_url',
    'currencies',
    'languages',
  ];

  protected $casts = [
    'currencies' => 'array',
    'languages' => 'array',
    'population' => 'integer',
  ];
}
