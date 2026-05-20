<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberPpab extends Model
{
    protected $connection = 'ppab';
    protected $table = 'ppab_member';
    protected $primaryKey = 'id_member';
    
    public $timestamps = false;
    protected $guarded = [];
}
