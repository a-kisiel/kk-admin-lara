<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    public $fillable = [
        'title',
        'location',
        'description',
        'start_date',
        'end_date'
    ];
}
