<?php

namespace App\Http\Controllers\API;

use App\Models\Piece;
use App\Models\Medium;
use App\Models\Collection;
use App\Models\Backup;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function getBackup()
    {
        $hash = Backup::latest()->pluck('hash')->first();
        return response()->download(Storage::url("backups/$hash.json"));
    }

    public function generateBackup()
    {
        $pieces = Piece::all();
        $media = Medium::all();
        $collections = Collection::all();

        $hash = md5(time());

        Backup::create([
            'hash' => $hash
        ]);

        Storage::put("/backups/$hash.json", json_encode([
            'pieces' => $pieces,
            'media' => $media,
            'collections' => $collections
        ]));

        Backup::prune();
    }
}
