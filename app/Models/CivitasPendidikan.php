<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CivitasPendidikan extends Model
{
    protected $table = 'civitas_pendidikans';
    
    protected $fillable = [
        'uuid',
        'source_type',
        'source_id',
        'level_angkatan',
        'paket',
    ];

    public function getMasterDataAttribute()
    {
        if ($this->source_type === 'table_ppab_baru') {
            // MemberPpab's declared primaryKey is id_member, but id_member is not
            // unique (duplicate registrations), so look up by the real unique id.
            return MemberPpab::where('id', $this->source_id)->first();
        } elseif ($this->source_type === 'table_member_lama') {
            // MemberLama uses auto-increment id as the identifier (member_no is not unique)
            return MemberLama::find($this->source_id);
        }
        return null;
    }

    public function getPhotoAttribute()
    {
        $master = $this->masterData;
        return $master ? $master->photo : null;
    }

    public function getNameAttribute()
    {
        $master = $this->masterData;
        if (!$master) return null;
        
        return $this->source_type === 'table_ppab_baru' 
            ? $master->name 
            : $master->member_name;
    }

    public function getGenderAttribute()
    {
        $master = $this->masterData;
        if (!$master) return null;
        
        return $this->source_type === 'table_ppab_baru' 
            ? $master->gender 
            : $master->member_gend;
    }

    public function getEmailAttribute()
    {
        $master = $this->masterData;
        if (!$master) return null;
        
        return $this->source_type === 'table_ppab_baru' 
            ? $master->email 
            : $master->member_emai;
    }
    public function getAngkatanAttribute()
    {
        $master = $this->masterData;
        if (!$master) return null;

        if ($this->source_type === 'table_ppab_baru') {
            return DB::connection('ppab')
                ->table('ppab_nama_angkatans')
                ->where('id', $master->angkatan)
                ->value('nama_angkatan');
        }

        return $master->member_nama_angkatan;
    }

    public function getPaketAttribute($value)
    {
        return $value;
    }

    public function mutabaahQurans()
    {
        return $this->hasMany(MutabaahQuran::class, 'civitas_id', 'uuid');
    }
}
