<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerifikasiSertifikat extends Model
{
    protected $table = 'verifikasi_sertifikat';

    protected $fillable = ['sertifikat_id', 'ip', 'user_agent'];

    public function sertifikat(): BelongsTo
    {
        return $this->belongsTo(Sertifikat::class, 'sertifikat_id');
    }
}
