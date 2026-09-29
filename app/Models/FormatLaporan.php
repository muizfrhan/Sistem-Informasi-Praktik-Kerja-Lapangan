<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class FormatLaporan extends Model
{
    protected $table = 'format_laporan';

    protected $fillable = ['nama', 'file'];

    public function url(): string
    {
        return Storage::disk('public')->url($this->file);
    }

    public function ekstensi(): string
    {
        return strtoupper(pathinfo((string) $this->file, PATHINFO_EXTENSION));
    }
}
