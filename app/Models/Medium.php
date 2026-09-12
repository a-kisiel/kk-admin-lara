<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medium extends Model
{
    public $fillable = ['title'];

    public function pieces()
    {
        return $this->belongsToMany(Piece::class, 'piece_media');
    }
}
