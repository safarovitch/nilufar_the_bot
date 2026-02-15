<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class MusicSearchService
{
    protected $api;

    public function __construct()
    {
        // Initialize Yandex Music API client
        $this->api = new \YandexMusic\Client();
    }

    /**
     * Get track details by ID (Yandex composite ID or other)
     */
    public function getTrackDetails(string $id): ?array
    {
        if (str_starts_with($id, 'ya:')) {
            // Parse ya:albumId:trackId
            $parts = explode(':', $id);
            if (count($parts) >= 3) {
                $trackId = $parts[2];
                $albumId = $parts[1];

                try {
                    $track = $this->api->getTrack($trackId);
                    if ($track) {
                        // Some API clients return array of tracks even for single get
                        if (is_array($track)) $track = $track[0];

                        // Determine artist name
                        $artists = [];
                        if (isset($track->artists)) {
                            foreach ($track->artists as $artist) {
                                $artists[] = $artist->name;
                            }
                        }
                        $artistName = !empty($artists) ? implode(', ', $artists) : 'Unknown Artist';

                        $webpageUrl = "https://music.yandex.ru/album/{$albumId}/track/{$trackId}";

                        return [
                            'id' => $id,
                            'title' => $track->title,
                            'duration' => $track->durationMs / 1000,
                            'uploader' => $artistName,
                            'webpage_url' => $webpageUrl,
                            'source' => 'yandex_music'
                        ];
                    }
                } catch (\Exception $e) {
                    Log::error("Yandex getTrack error: " . $e->getMessage());
                }
            }
        }

        // Fallback or generic ID (maybe YouTube) - simplistic fallback
        return [
            'id' => $id,
            'title' => 'Unknown Title',
            'uploader' => 'Unknown Artist',
            'duration' => 0
        ];
    }

    /**
     * Search for music using Yandex Music.
     *
     * @param string $query
     * @return array
     */
    public function search(string $query): array
    {
        Log::info("MusicSearchService: Searching Yandex Music for '$query'");

        try {
            // Search for tracks
            $searchResult = $this->api->search($query);

            // Check if we have results
            if (!$searchResult || !isset($searchResult->best) || !isset($searchResult->best->result)) {
                // Fallback to tracks list if 'best' is not a track or missing
                if (isset($searchResult->tracks) && isset($searchResult->tracks->results)) {
                    $tracks = array_slice($searchResult->tracks->results, 0, 5);
                } else {
                    Log::warning("MusicSearchService: No results found for '$query'");
                    return [];
                }
            } else {
                // If 'best' match is a track, put it first, then add others
                $best = $searchResult->best->result;
                $tracks = [$best];

                if (isset($searchResult->tracks) && isset($searchResult->tracks->results)) {
                    $moreTracks = array_slice($searchResult->tracks->results, 0, 4);
                    $tracks = array_merge($tracks, $moreTracks);
                }
            }

            $results = [];

            foreach ($tracks as $track) {
                // Determine artist name
                $artists = [];
                if (isset($track->artists)) {
                    foreach ($track->artists as $artist) {
                        $artists[] = $artist->name;
                    }
                }
                $artistName = !empty($artists) ? implode(', ', $artists) : 'Unknown Artist';

                // Yandex Music Track ID
                $trackId = $track->id;

                // Construct a URL that yt-dlp can handle
                // yt-dlp supports https://music.yandex.ru/album/{albumId}/track/{trackId}
                $albumId = isset($track->albums[0]) ? $track->albums[0]->id : null;

                if ($trackId && $albumId) {
                    $webpageUrl = "https://music.yandex.ru/album/{$albumId}/track/{$trackId}";
                    // Composite ID for callback data (limit 64 chars)
                    // Format: yandex:albumId:trackId
                    $compositeId = "ya:{$albumId}:{$trackId}";

                    $results[] = [
                        'id' => $compositeId,
                        'title' => $track->title,
                        'duration' => $track->durationMs / 1000, // Duration is in ms
                        'uploader' => $artistName,
                        'webpage_url' => $webpageUrl,
                        'source' => 'yandex_music'
                    ];
                }
            }

            return $results;
        } catch (\Exception $e) {
            Log::error('MusicSearchService Yandex Error: ' . $e->getMessage());
            return [];
        }
    }
}
