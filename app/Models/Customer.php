<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customers';
    protected $fillable = ['name','nama','photo_blob','photo_path','alamat','province_name','regency_name','district_name','kodepos'];
    public $timestamps = true;
}
