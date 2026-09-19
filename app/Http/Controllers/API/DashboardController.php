<?php

namespace App\Http\Controllers\API;

use App\Models\Piece;
use App\Models\Book;
use App\Models\Sketch;
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
    /**
     * Make a json file with everything in it and place it in the backups folder
     */
    public function generateJSON()
    {
        $pieces = Piece::with(['media', 'collections', 'children'])->get();
        $books = Book::with(['media', 'children'])->get();
        $sketches = Sketch::with(['media'])->get();
        $media = Medium::all();
        $collections = Collection::all();

        $aps = 
        $abs = 
        $ass = [];

        foreach($pieces as $k => $piece) {
            $ap = $piece->toArray();
            $ap['media'] = $piece->media->pluck('id');
            $ap['collections'] = $piece->collections->pluck('id');
            $ap['children'] = $piece->children->pluck('id');
            $aps[] = $ap;
        }
        foreach($books as $k => $book) {
            $ab = $book->toArray();
            $ab['media'] = $book->media->pluck('id');
            $ab['children'] = $book->children->pluck('id');
            $abs[] = $ab;
        }
        foreach($sketches as $k => $sketch) {
            $as = $sketch->toArray();
            $as['media'] = $sketch->media->pluck('id');
            $ass[] = $as;
        }

        $hash = md5(time());

        $data = json_encode([
            'pieces' => $aps,
            'books' => $abs,
            'sketches' => $ass,
            'media' => $media,
            'collections' => $collections
        ]);

        $file = "/pending/$hash.json";

        Storage::put($file, $data);
    }

    /**
     * Get the most recent file in the backups folder and 
     */
    public function pushChanges()
    {
        $pending = Storage::files('pending');
        $latest = collect($pending)->sortByDesc(fn ($f) => Storage::lastModified($f))->first();
        
        // Move current to backup
        $hash = md5(time());
        Storage::move('metadata.json', "backups/$hash.json");

        // Set newest in pending/ to current
        Storage::move($latest, 'metadata.json');
    }

    public function revertChanges()
    {
        $backups = Storage::files('backup');
        $latest = collect($backups)->sortByDesc(fn ($f) => Storage::lastModified($f))->first();

        // Overwrite
        Storage::move($latest, 'metadata.json');
    }

    public function rebuildDB(Request $request)
    {
        $data = $request->file('db_file')->get();
        $data = json_decode($data, true);

        Piece::truncate();
        Book::truncate();
        Sketch::truncate();
        Medium::truncate();
        Collection::truncate();

        DB::table('piece_media')->truncate();
        DB::table('collection_pieces')->truncate();
        DB::table('book_media')->truncate();
        DB::table('sketch_media')->truncate();

        $pieces = $data['pieces'];
        $books = $data['books'];
        $sketched = $data['sketches'];
        $media = $data['media'];
        $collections = $data['collections'];

        $pc =
        $bc =
        $sc =
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

        foreach($books as $book) {
            $b = Book::create($book);
            if (!empty($book['media']))
                $b->media()->attach($book['media']);
            $bc++;
        }
        foreach($sketches as $sketch) {
            $s = Sketch::create($sketch);
            if (!empty($sketch['media']))
                $s->media()->attach($sketch['media']);
            $sc++;
        }

        // Account for older convention
        foreach($related as $hash => $children) {
            $parent_id = Piece::where('hash', $hash)->pluck('id')->first();
            Piece::whereIn('id', $children)->update(['parent_id' => $parent_id]);
        }

        return response("
            DB successfully overwritten:
            \n\n
            Created $pc pieces, $bc books, $sc sketches, $mc media, and $cc collections
        ");
    }

    public function generateThumbnails()
    {
        foreach(Piece::whereNull('parent_id')->get() as $piece)
            if (empty($piece->image_color))
                $piece->generateThumbnails();
        foreach(Book::whereNull('parent_id')->get() as $book)
            if (empty($book->image_color))
                $book->generateThumbnails();
        foreach(Sketch::whereNull('parent_id')->get() as $sketch)
            if (empty($sketch->image_color))
                $sketch->generateThumbnails();
    }
}
