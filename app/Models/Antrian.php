<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Antrian extends Model
{
    protected $table = 'antrian';

    protected $fillable = ['nama', 'nomor', 'status'];

    protected $casts = ['nomor' => 'integer'];
}
