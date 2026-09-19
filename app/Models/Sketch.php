<?php

namespace App\Models;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class Sketch extends Model
{
    public $fillable = [
        'title',
        'description',
        'hash',
        'image_width',
        'image_height',
        'image_color',
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
            'active' => 'boolean'
        ];
    }

    public function media()
    {
        return $this->belongsToMany(Medium::class, 'sketch_media');
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
        $existing = Sketch::pluck('hash');

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

    public function addImage($file, $hash, $ext)
    {
        $dir = 'sketches/' . ($ext === 'jpg' ? 'uncompressed' : 'compressed');

        Storage::put("$dir/$hash.$ext", file_get_contents($file));
    }

    public function generateThumbnails(Request $request = null)
    {
        // Thumbnails are being generated after the fact from existing files
        if (empty($request)) {
            $uncompressed = Image::fromStorage("sketches/uncompressed/$this->hash.jpg");
            $thumbnail = $uncompressed->scale(width: 50);
        }
        else {
        }

        $data = [];

        if (empty($this->image_width))
            $data['image_width'] = $uncompressed->width();
        if (empty($this->image_height))
            $data['image_height'] = $uncompressed->height();
        if (empty($this->image_color))
            $data['image_color'] = $uncompressed->dominantColor();

        $thumbnail->storeAs(path: 'thumbnails/sketches', name: "$this->hash.jpg");
        $this->update($data);
    }

    public function deleteImages()
    {
        Storage::delete("sketches/uncompressed/$this->hash.jpg");
        Storage::delete("sketches/compressed/$this->hash.webp");
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
                if ($has_images)
                    $c->deleteImages();
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