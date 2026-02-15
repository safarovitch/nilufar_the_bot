<?php

namespace App\Services;

use App\Models\PlayedTrack;
use Illuminate\Support\Facades\Log;

class HistoryService
{
    /**
     * Log a track as played.
     *
     * @param int|string $userId Local User ID or Telegram User ID (if we decide to store string)
     * @param array $trackData [track_source_id, title, artist, genre]
     * @return void
     */
    public function logPlayedTrack($userId, array $trackData): void
    {
        try {
            PlayedTrack::create([
                'user_id' => is_numeric($userId) ? $userId : null, // Handle mapping later if needed
                'track_source_id' => $trackData['track_source_id'],
                'title' => $trackData['title'],
                'artist' => $trackData['artist'] ?? null,
                'genre' => $trackData['genre'] ?? null,
                'played_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log played track: ' . $e->getMessage());
        }
    }

    /**
     * Fetch external history (YouTube/Yandex).
     *
     * @param int $userId
     * @param string $provider
     * @return array
     */
    public function fetchExternalHistory(int $userId, string $provider): array
    {
        // Implementation will go here
        return [];
    }
}
