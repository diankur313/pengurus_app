<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PpabRegistration extends Model
{
    protected $connection = 'ppab';
    protected $table = 'ppab_sessions';
    protected $guarded = [];
}
