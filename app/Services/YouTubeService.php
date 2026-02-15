<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YouTubeService
{
    /**
     * Fetch recently played or liked videos from YouTube.
     * Note: 'history' scope is very restricted. We often use 'liked' videos or playlists.
     *
     * @param string $accessToken
     * @return array
     */
    public function fetchHistory(string $accessToken): array
    {
        // Try to fetch liked videos as a proxy for history/preferences
        $url = 'https://www.googleapis.com/youtube/v3/videos';

        $response = Http::withToken($accessToken)->get($url, [
            'myRating' => 'like',
            'part' => 'snippet,contentDetails',
            'maxResults' => 10,
        ]);

        if ($response->successful()) {
            $items = $response->json()['items'] ?? [];
            return array_map(function ($item) {
                return [
                    'track_source_id' => $item['id'],
                    'title' => $item['snippet']['title'],
                    'artist' => $item['snippet']['channelTitle'], // Best guess for artist
                    'genre' => null, // YouTube API doesn't give genre easily per video
                    'played_at' => now(), // Approximate
                ];
            }, $items);
        }

        Log::error('YouTube API Error: ' . $response->body());
        return [];
    }
}
