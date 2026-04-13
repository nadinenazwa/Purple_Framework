<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    // Table matches existing DB (phpMyAdmin shows columns like idmenu, nama_menu, harga, idvendor)
    protected $table = 'menu';
    protected $primaryKey = 'idmenu';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = ['idvendor', 'nama_menu', 'harga', 'path_gambar'];

    // Provide accessors so existing controller/views can use `name`, `price`, `user_id`
    public function getNameAttribute()
    {
        return $this->attributes['nama_menu'] ?? null;
    }

    public function setNameAttribute($value)
    {
        $this->attributes['nama_menu'] = $value;
    }

    public function getPriceAttribute()
    {
        return $this->attributes['harga'] ?? null;
    }

    public function setPriceAttribute($value)
    {
        $this->attributes['harga'] = $value;
    }

    public function getUserIdAttribute()
    {
        return $this->attributes['idvendor'] ?? null;
    }

    public function setUserIdAttribute($value)
    {
        $this->attributes['idvendor'] = $value;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'idvendor');
    }
}
