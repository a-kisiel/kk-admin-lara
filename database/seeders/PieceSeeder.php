<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Piece;
use App\Models\Medium;
use App\Models\Collection;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PieceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $metadata = Storage::disk('s3')->get('metadata.json');
        $metadata = json_decode($metadata, true);

        Log::debug($metadata);

        $related = [];

        foreach($metadata['pieces'] as $hash => $piece) {
            $p = Piece::create([
                'title' => $piece['title'],
                'description' => $piece['description'] ?? '',
                'hash' => $hash,
                'start_date' => $piece['date'],
                'actual_size' => $piece['size'] ?? '',
                'is_wallpaper' => !empty($piece['wallpaper'])
            ]);

            $media = $piece['media'];
            $collections = $piece['collections'] ?? [];

            foreach($media as $k => $medium)
                $media[$k]++;
            foreach($collections as $k => $collection)
                $collections[$k]++;

            if (!empty($media))
                $p->media()->attach(Medium::whereIn('id', $media)->pluck('id'));
            if (!empty($collections))
                $p->collections()->attach(Collection::whereIn('id', $collections)->pluck('id'));

            if (!empty($piece['related']))
                $related[$p->id] = $piece['related'];
        }

        foreach($related as $child_id => $parent_hash) {
            $p = Piece::where('hash', $parent_hash)->get()->first();
            if ($p)
                Piece::where('id', $child_id)->update(['parent_id' => $p->id]);
        }
    }
}
