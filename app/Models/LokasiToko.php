<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LokasiToko extends Model
{
    protected $table      = 'lokasi_toko';
    protected $primaryKey = 'barcode';
    public    $incrementing = false;
    protected $keyType    = 'string';

    protected $fillable = [
        'barcode',
        'nama_toko',
        'latitude',
        'longitude',
        'accuracy',
    ];

    public function kunjungan()
    {
        return $this->hasMany(KunjunganToko::class, 'barcode_toko', 'barcode');
    }
}
