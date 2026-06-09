<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    protected $fillable = ['nim', 'nama', 'nfc_serial'];

    public function absensis()
    {
        return $this->hasMany(Absensi::class);
    }
}
