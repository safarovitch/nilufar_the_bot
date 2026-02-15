<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayedTrack extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'track_source_id',
        'title',
        'artist',
        'genre',
        'played_at',
    ];

    // If we need to cast played_at
    protected $casts = [
        'played_at' => 'datetime',
    ];
}
