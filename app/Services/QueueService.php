<?php

namespace App\Services;

use App\Models\PlaybackQueue;
use App\Models\PlayedTrack;
use Illuminate\Support\Facades\DB;

class QueueService
{
    /**
     * Add a track to the user's queue.
     *
     * @param int|string $userId
     * @param array $trackData [track_source_id, title, artist, duration]
     * @return PlaybackQueue
     */
    public function addToQueue($userId, array $trackData): PlaybackQueue
    {
        // Calculate next position
        $maxPosition = PlaybackQueue::where('user_id', $userId)->max('position') ?? 0;

        return PlaybackQueue::create([
            'user_id' => $userId,
            'track_source_id' => $trackData['track_source_id'],
            'title' => $trackData['title'] ?? 'Unknown Title',
            'artist' => $trackData['artist'] ?? 'Unknown Artist',
            'duration' => $trackData['duration'] ?? 0,
            'position' => $maxPosition + 1,
        ]);
    }

    /**
     * Get the next track from the queue and remove it.
     *
     * @param int|string $userId
     * @return PlaybackQueue|null
     */
    public function popNextTrack($userId): ?PlaybackQueue
    {
        return DB::transaction(function () use ($userId) {
            $nextTrack = PlaybackQueue::where('user_id', $userId)
                ->orderBy('position', 'asc')
                ->first();

            if ($nextTrack) {
                $nextTrack->delete();
            }

            return $nextTrack;
        });
    }

    /**
     * Get the current queue for a user.
     *
     * @param int|string $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getQueue($userId)
    {
        return PlaybackQueue::where('user_id', $userId)
            ->orderBy('position', 'asc')
            ->get();
    }

    /**
     * Clear the user's queue.
     *
     * @param int|string $userId
     * @return void
     */
    public function clearQueue($userId): void
    {
        PlaybackQueue::where('user_id', $userId)->delete();
    }
}
