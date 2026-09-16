<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medium extends Model
{
    public $fillable = ['title', 'is_support'];

    public function casts(): array
    {
        return [
            'is_support' => 'boolean',
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
}
