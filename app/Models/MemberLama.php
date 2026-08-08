<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberLama extends Model
{
    protected $connection = 'yisic_db_lama';
    protected $table = 'member';

    public $timestamps = false;
    protected $guarded = [];
}
