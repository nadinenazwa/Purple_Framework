<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;
    protected $table = 'barang';
    // primary key on this table is `id_barang` (varchar), not the default `id`
    protected $primaryKey = 'id_barang';
    public $incrementing = false;
    protected $keyType = 'string';
    // the existing table uses a single `timestamp` column, so disable Eloquent's automatic timestamps
    public $timestamps = false;

    protected $fillable = [
        'id_barang',
        'nama',
        'kategori',
        'harga',
    ];

    // Make route-model binding use the primary key `id_barang`
    public function getRouteKeyName()
    {
        return 'id_barang';
    }
}
