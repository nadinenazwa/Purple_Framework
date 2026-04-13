<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Buku extends Model
{
    use HasFactory;

    protected $table = 'buku';
    protected $primaryKey = 'idbuku';
    public $timestamps = false;
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'kode',
        'judul',
        'pengarang',
        'idkategori',
    ];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'idkategori');
    }
}
