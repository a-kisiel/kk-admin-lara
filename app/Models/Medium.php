<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medium extends Model
{
    public $fillable = ['title', 'type', 'active'];
    
    public function casts(): array
    {
        return [
            'active' => 'boolean'
        ];
    }

    public function pieces()
    {
        return $this->belongsToMany(Piece::class, 'piece_media');
    }

    public function supportPieces()
    {
        return $this->hasMany(Piece::class, 'support_id');
    }

    public function books()
    {
        return $this->belongsToMany(Book::class, 'book_media');
    }

    public function sketches()
    {
        return $this->belongsToMany(Sketch::class, 'sketch_media');
    }

    public function getTypeLabelAttribute()
    {
        return config('enums.media_types')[$this->type];
    }
}
