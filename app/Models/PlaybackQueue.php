<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlaybackQueue extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'track_source_id',
        'title',
        'artist',
        'duration',
        'position',
    ];
}
