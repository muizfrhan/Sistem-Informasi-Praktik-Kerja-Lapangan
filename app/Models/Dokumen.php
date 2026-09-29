<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Dokumen extends Model
{
    protected $table = 'dokumen';

    protected $fillable = [
        'user_id', 'nama', 'path', 'disk', 'mime', 'ukuran', 'tipe',
        'related_type', 'related_id',
    ];

    protected $casts = ['ukuran' => 'integer'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function url(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }
}
