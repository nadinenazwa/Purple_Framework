<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KunjunganToko extends Model
{
    protected $table = 'kunjungan_toko';

    protected $fillable = [
        'barcode_toko',
        'nama_toko',
        'lat_toko',
        'lng_toko',
        'acc_toko',
        'lat_sales',
        'lng_sales',
        'acc_sales',
        'jarak_meter',
        'threshold_efektif',
        'status',
    ];

    public function toko()
    {
        return $this->belongsTo(LokasiToko::class, 'barcode_toko', 'barcode');
    }
}
