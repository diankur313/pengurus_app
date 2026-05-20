<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CivitasPendidikan extends Model
{
    protected $table = 'civitas_pendidikans';
    
    protected $fillable = [
        'uuid',
        'source_type',
        'source_id',
        'level_angkatan',
    ];

    public function getMasterDataAttribute()
    {
        if ($this->source_type === 'table_ppab_baru') {
            return MemberPpab::find($this->source_id);
        } elseif ($this->source_type === 'table_member_lama') {
            // MemberLama uses member_no as the identifier
            return MemberLama::where('member_no', $this->source_id)->first();
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
        
        return $this->source_type === 'table_ppab_baru' 
            ? $master->nama_angkatan 
            : $master->member_nama_angkatan;
    }
}
