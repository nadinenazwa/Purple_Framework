<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    // Tambahkan baris ini untuk menentukan nama tabel yang benar
    protected $table = 'kategori'; 

    protected $primaryKey = 'idkategori'; 
    protected $fillable = ['nama_kategori'];

    public function getRouteKeyName()
    {
        return $this->primaryKey;
    }
}

