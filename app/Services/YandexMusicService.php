<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YandexMusicService
{
    /**
     * Fetch user's liked tracks from Yandex Music.
     *
     * @param string $accessToken
     * @return array
     */
    public function fetchHistory(string $accessToken): array
    {
        // Yandex Music API (Unofficial/Official mobile API style)
        // Usually requires fetching user info first to get UID, then likes.
        // Endpoint: https://api.music.yandex.net/users/{userId}/likes/tracks

        // 1. Get User ID
        $userResponse = Http::withToken($accessToken)
            ->get('https://login.yandex.ru/info');

        if ($userResponse->failed()) {
            Log::error('Yandex Auth Info failed');
            return [];
        }

        $yandexUserId = $userResponse->json()['id'] ?? null;
        if (!$yandexUserId) return [];

        // 2. Get Likes
        // Note: The OAuth token from 'socialiteproviders/yandex' is usually for Yandex Passport.
        // It might not work directly for https://api.music.yandex.net without specific scopes or using the specific Music API flow.
        // However, many implementations use the same token if scopes are correct.
        // Assuming we have access (scope: music:content)

        $url = "https://api.music.yandex.net/users/$yandexUserId/likes/tracks";

        $response = Http::withToken($accessToken)->get($url);

        if ($response->successful()) {
            $data = $response->json();
            $tracks = $data['result']['library']['tracks'] ?? []; // Structure varies, approximating

            // We might need to fetch track details for each ID if the list is just IDs.
            // But let's assume we get objects or implement detail fetching later.
            // For this skeletal implementation, we return empty if structure mismatch.

            $formatted = [];
            foreach ($tracks as $trackItem) {
                // Fetch track info if needed, or if provided:
                $track = $trackItem['track'] ?? $trackItem; // sometimes nested

                $formatted[] = [
                    'track_source_id' => $track['id'] ?? 'unknown',
                    'title' => $track['title'] ?? 'Unknown',
                    'artist' => $track['artists'][0]['name'] ?? 'Unknown',
                    'genre' => null,
                    'played_at' => now(),
                ];
            }

            return $formatted;
        }

        Log::error('Yandex Music API Error: ' . $response->body());
        return [];
    }
}
