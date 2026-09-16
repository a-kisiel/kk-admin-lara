<?php

namespace App\Models;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class Piece extends Model
{
    public $fillable = [
        'title',
        'description',
        'dimensions',
        'hash',
        'location',
        'start_date',
        'end_date',
        'support_id',
        'active',
        'is_wallpaper',
        'parent_id',
        'compressed',
        'uncompressed'
    ];

    public function casts(): array
    {
        return [
            'active' => 'boolean',
            'is_wallpaper' => 'boolean'
        ];
    }

    public function media()
    {
        return $this->belongsToMany(Medium::class, 'piece_media');
    }

    public function supportMedium()
    {
        return $this->belongsTo(Medium::class, 'support_id');
    }

    public function collections()
    {
        return $this->belongsToMany(Collection::class, 'collection_pieces');
    }

    public function children()
    {
        return $this->hasMany($this, 'parent_id');
    }

    public function getStubAttribute()
    {
        return config('filesystems.disks.' . config('filesystems.default') . '.url');
    }

    public static function getNewHash(): String
    {
        $existing = Piece::pluck('hash');

        $hash = null;
        while (!$hash || $existing->contains($hash)) {
            $hash = strtoupper(bin2hex(random_bytes(6)));
        }

        return $hash;
    }

    public function updateMedia($media)
    {
        $media = json_decode($media, true);

        $existing_media = $this->media->pluck('id');

        $media_to_add = 
        $media_to_remove = 
        $media_ids = [];

        foreach($media as $medium) {
            $media_ids[] = $medium['value'];
            if (!$existing_media->contains($medium['value']))
                $media_to_add[] = $medium['value'];
        }
        foreach($existing_media as $medium)
            if (!in_array($medium, $media_ids))
                $media_to_remove[] = $medium;

        $this->media()->attach(Medium::whereIn('id', $media_to_add)->pluck('id'));
        $this->media()->detach(Medium::whereIn('id', $media_to_remove)->pluck('id'));
    }

    public function updateCollections($collections)
    {
        $collections = json_decode($collections, true);

        $existing_collections = $this->collections->pluck('id');

        $collections_to_add = 
        $collections_to_remove =
        $collection_ids = [];

        foreach($collections as $collection) {
            $collection_ids[] = $collection['value'];
            if (!$existing_collections->contains($collection['value']))
                $collections_to_add[] = $collection['value'];
        }
        foreach($existing_collections as $collection)
            if (!in_array($collection, $collection_ids))
                $collections_to_remove[] = $collection;

        $this->collections()->attach(Collection::whereIn('id', $collections_to_add)->pluck('id'));
        $this->collections()->detach(Collection::whereIn('id', $collections_to_remove)->pluck('id'));
    }

    public function addImage($file, $hash, $ext)
    {
        $dir = 'hashed_' . ($ext === 'jpg' ? 'uncompressed' : 'compressed');

        Storage::put("$dir/$hash.$ext", file_get_contents($file));

        // Remove existing image
        if (!empty($this->hash))
            Storage::delete("$dir/$this->hash.$ext");
    }

    public function handleChildren($request, $children)
    {
        $existing = $this->children()->pluck('id')->toArray();
        $remaining = [];

        foreach($children as $id => $child) {
            $has_images = $request->hasFile("children.$id.compressed") && $request->hasFile("children.$id.uncompressed");          
            if ($has_images)
                $child['hash'] = $this->getNewHash();

            // Create new
            if (strstr($id, 'new')) {
                $child['parent_id'] = $this->id;
                $c = $this->create($child);
            }
            // Update existing
            else {
                $remaining[] = $id;
                $c = $this->findOrFail($id);
                $c->update($child);
            }
            
            if ($has_images) {
                $uncompressed = $request->file("children.$id.uncompressed");
                $compressed = $request->file("children.$id.compressed");

                $c->addImage($uncompressed, $child['hash'], 'jpg');
                $c->addImage($compressed, $child['hash'], 'webp');
            }
        }

        $deleted = array_diff($existing, $remaining);
        $this->whereIn('id', $deleted)->delete();
    }
}