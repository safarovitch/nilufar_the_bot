<?php

namespace App\Services;

use App\Models\PlayedTrack;
use Illuminate\Support\Facades\DB;

class RecommendationService
{
    /**
     * Get music recommendations for a user.
     *
     * @param int|string $userId
     * @return array
     */
    public function getRecommendations($userId): array
    {
        // Simple logic: 
        // 1. Get user's most played genres/artists
        // 2. Find other tracks with same genre/artist from global history (collaborative filtering placeholder)
        // 3. Or just random tracks if history is empty.

        // For now, let's just return top played tracks from global history that the user hasn't played recently?
        // Or simply: "You liked 'Artist', here is another track by 'Artist' or similar."

        // Let's implement a simple "Most Popular" or "Similar to recent" strategy.

        $recentTracks = PlayedTrack::where('user_id', $userId)
            ->latest('played_at')
            ->take(5)
            ->get();

        if ($recentTracks->isEmpty()) {
            // New user: return global popular tracks
            return $this->getGlobalSemularTracks();
        }

        // Get genres/artists from recent tracks
        $artists = $recentTracks->pluck('artist')->filter()->unique()->toArray();
        $genres = $recentTracks->pluck('genre')->filter()->unique()->toArray();

        // Find tracks matching these artists/genres
        $query = PlayedTrack::query()
            ->where('user_id', '!=', $userId) // Exclude own plays if we assume these are just logs
            // actually played_tracks is a log. We want "Tracks" entity ideally.
            // But we can recommend from what others played.
            ->where(function ($q) use ($artists, $genres) {
                if (!empty($artists)) $q->orWhereIn('artist', $artists);
                if (!empty($genres)) $q->orWhereIn('genre', $genres);
            })
            ->select('track_source_id', 'title', 'artist')
            ->distinct()
            ->limit(5);

        $recommendations = $query->get()->toArray();

        if (empty($recommendations)) {
            return $this->getGlobalSemularTracks();
        }

        return $recommendations;
    }

    protected function getGlobalSemularTracks()
    {
        // Fallback: get random recent tracks from anyone
        return PlayedTrack::query()
            ->inRandomOrder()
            ->limit(5)
            ->get(['track_source_id', 'title', 'artist'])
            ->toArray();
    }
}
