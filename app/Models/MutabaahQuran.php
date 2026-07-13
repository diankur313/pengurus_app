<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MutabaahQuran extends Model
{
    protected $table = 'mutabaah_qurans';

    protected $fillable = [
        'civitas_id',
        'pertama_setor',
        'from_surah',
        'from_ayat',
        'to_surah',
        'to_ayat',
        'total_halaman',
        'total_juz',
    ];

    protected $casts = [
        'pertama_setor' => 'date',
        'from_ayat' => 'integer',
        'to_ayat' => 'integer',
        'total_halaman' => 'integer',
        'total_juz' => 'decimal:2',
    ];

    public function civitas(): BelongsTo
    {
        return $this->belongsTo(CivitasPendidikan::class, 'civitas_id', 'uuid');
    }
}