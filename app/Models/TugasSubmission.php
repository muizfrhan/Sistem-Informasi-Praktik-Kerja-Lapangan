<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TugasSubmission extends Model
{
    protected $table = 'tugas_submission';

    protected $fillable = [
        'tugas_id', 'mahasiswa_id', 'file', 'catatan', 'status',
        'nilai', 'feedback', 'reviewed_by', 'reviewed_at', 'submitted_at',
    ];

    protected $casts = [
        'file' => 'array',
        'nilai' => 'decimal:2',
        'reviewed_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    public function tugas(): BelongsTo
    {
        return $this->belongsTo(Tugas::class, 'tugas_id');
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
