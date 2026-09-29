<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SidangPenguji extends Model
{
    protected $table = 'sidang_penguji';

    protected $fillable = [
        'sidang_id', 'user_id', 'nama', 'jabatan', 'nilai_presentasi', 'catatan', 'feedback',
    ];

    protected $casts = ['nilai_presentasi' => 'decimal:2'];

    public function sidang(): BelongsTo
    {
        return $this->belongsTo(Sidang::class, 'sidang_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
