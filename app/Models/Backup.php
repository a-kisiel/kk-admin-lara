<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;

use Illuminate\Database\Eloquent\Model;

class Backup extends Model
{
    public $fillable = [
        'hash'
    ];

    public static function prune()
    {
        // Only keep 10 backups at a time
        $outdated = Backup::latest()->limit(1)->skip(10)->pluck('hash');
        Storage::delete("/backups/$outdated.json");
        Backup::latest()->limit(1)->skip(10)->delete();
    }
}
