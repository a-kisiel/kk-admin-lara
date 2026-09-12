<?php

namespace App\Http\Controllers\API;

use App\Models\Piece;
use App\Models\Medium;
use App\Models\Collection;
use App\Models\Backup;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function getBackup()
    {
        $hash = Backup::latest()->pluck('hash')->first();
        return response()->download(Storage::url("backups/$hash.json"));
    }

    public function generateBackup()
    {
        $pieces = Piece::with(['media', 'collections'])->get();
        $media = Medium::all();
        $collections = Collection::all();

        $aps = [];

        foreach($pieces as $k => $piece) {
            $ap = $piece->toArray();
            $ap['media'] = $piece->media->pluck('id');
            $ap['collections'] = $piece->collections->pluck('id');
            $ap['children'] = $piece->children->pluck('id');
            $aps[] = $ap;
        }

        $hash = md5(time());

        Backup::create([
            'hash' => $hash
        ]);

        $push_to_live = true;
        $file = $push_to_live ?
            "metadata.json" :
            "/backups/$hash.json";
        
        Storage::put($file, json_encode([
            'pieces' => $aps,
            'media' => $media,
            'collections' => $collections
        ]));

        Backup::prune();
    }

    public function rebuildDB(Request $request)
    {
        $data = $request->file('db_file')->get();
        $data = json_decode($data, true);

        Piece::truncate();
        Medium::truncate();
        Collection::truncate();

        DB::table('piece_media')->truncate();
        DB::table('collection_pieces')->truncate();

        $pieces = $data['pieces'];
        $media = $data['media'];
        $collections = $data['collections'];

        $pc =
        $mc =
        $cc = 0;

        foreach($media as $medium) {
            Medium::create(['title' => $medium]);
            $mc++;
        }

        foreach($collections as $collection) {
            Collection::create($collection);
            $cc++;
        }

        $related = [];

        foreach($pieces as $hash => $piece) {
            $piece['hash'] = $hash;
            $p = Piece::create($piece);
            if (!empty($piece['media']))
                $p->media()->attach($piece['media']);
            if (!empty($piece['collections']))
                $p->collections()->attach($piece['collections']);
            
            if (!empty($piece['related'])) {
                if (empty($related[$piece['related']]))
                    $related[$piece['related']] = [];
                $related[$piece['related']][] = $p->id;
            }
            $pc++;
        }

        // Account for older convention
        foreach($related as $hash => $children) {
            $parent_id = Piece::where('hash', $hash)->pluck('id')->first();
            Piece::whereIn('id', $children)->update(['parent_id' => $parent_id]);
        }

        return response("
            DB successfully overwritten:
            \n\n
            Created $pc pieces, $mc media, and $cc collections
        ");
    }
}
