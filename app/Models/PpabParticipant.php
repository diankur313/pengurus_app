<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PpabParticipant extends Model
{
    protected $connection = 'ppab';
    protected $table = 'ppab_member';
    protected $guarded = [];
}
