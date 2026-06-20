<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PpabNamaAngkatan extends Model
{
    protected $connection = 'ppab';
    protected $table = 'ppab_nama_angkatans';
    protected $guarded = [];
}
