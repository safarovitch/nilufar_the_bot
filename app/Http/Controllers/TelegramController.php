<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function webhook(Request $request)
    {
        $update = Telegram::commandsHandler(true);

        // Handle Callback Query (Inline Button Clicks)
        if ($update->has('callback_query')) {
            $callbackQuery = $update->getCallbackQuery();
            $data = $callbackQuery->getData();
            $chatId = $callbackQuery->getMessage()->getChat()->getId();

            if (str_starts_with($data, 'd:')) {
                $videoId = substr($data, 2);
                Telegram::answerCallbackQuery([
                    'callback_query_id' => $callbackQuery->getId(),
                    'text' => 'Downloading...',
                ]);
                Telegram::sendMessage(['chat_id' => $chatId, 'text' => 'Download started. Please wait...']);
                \App\Jobs\DownloadJob::dispatch($chatId, $videoId);
            } elseif (str_starts_with($data, 'q:')) {
                $videoId = substr($data, 2);

                // We need metadata for the queue. ideally we fetch it or pass it.
                // For now, let's just fetch basic info again or use placeholders if we want speed.
                // Better: Dispatch a job to fetch info and add to queue? Or just resolve immediately.
                // Let's use MusicSearchService to get details quickly or pass them if possible (too long for callback data).
                // We'll trust the ID and fetch details in background or just add with placeholder.
                // Let's resolve via service for better UX.

                $service = app(\App\Services\MusicSearchService::class);
                // NOTE: Search might be slow. Optimization: Cache search results keying by ID?
                // For MVP: let's just add with "Unknown" and let DownloadJob update it?
                // Actually queue needs display. Let's try to fetch synchronously for now (might timeout but ok for MVP).

                // Or better: Just confirm and let a job handle the "Add to Queue" which might take a second.
                Telegram::answerCallbackQuery([
                    'callback_query_id' => $callbackQuery->getId(),
                    'text' => 'Adding to queue...',
                ]);

                // Dispatch a job to add to queue so we don't block webhook
                // We need a new Job: AddToQueueJob? Or just do it inline if fast enough.
                // Let's try inline for now, if slow we move to job.
                try {
                    // Fetch basic info (should be fast if cached or single video lookup)
                    // searching by ID usually works for yt-dlp `ytsearch1:ID` or just ID
                    // Fetch basic info
                    $track = $service->getTrackDetails($videoId) ?? ['id' => $videoId, 'title' => 'Unknown Title', 'artist' => 'Unknown Artist'];

                    $queueService = app(\App\Services\QueueService::class);
                    $queueService->addToQueue($chatId, [
                        'track_source_id' => $track['id'],
                        'title' => $track['title'],
                        'artist' => $track['uploader'] ?? $track['artist'] ?? 'Unknown',
                        'duration' => $track['duration'] ?? 0,
                    ]);

                    Telegram::sendMessage(['chat_id' => $chatId, 'text' => "Added to queue: {$track['title']}"]);
                } catch (\Exception $e) {
                    Telegram::sendMessage(['chat_id' => $chatId, 'text' => 'Failed to add to queue.']);
                }
            } elseif ($data === 'next') {
                \Illuminate\Support\Facades\Log::info("Handling next callback for chat $chatId");

                Telegram::answerCallbackQuery([
                    'callback_query_id' => $callbackQuery->getId(),
                    'text' => 'Playing next...',
                ]);

                // Quick implementation inline:
                $queueService = app(\App\Services\QueueService::class);
                $nextTrack = $queueService->popNextTrack($chatId);

                \Illuminate\Support\Facades\Log::info("Next track: " . ($nextTrack ? $nextTrack->id : 'none'));

                if ($nextTrack) {
                    Telegram::sendMessage(['chat_id' => $chatId, 'text' => "Playing next: {$nextTrack->title}"]);
                    \App\Jobs\DownloadJob::dispatch($chatId, $nextTrack->track_source_id);
                } else {
                    // Recommendation
                    $recService = app(\App\Services\RecommendationService::class);
                    $recs = $recService->getRecommendations($chatId);
                    if (!empty($recs)) {
                        $track = $recs[0];
                        Telegram::sendMessage(['chat_id' => $chatId, 'text' => "Queue empty. Playing recommendation: {$track['title']}"]);
                        \App\Jobs\DownloadJob::dispatch($chatId, $track['track_source_id']);
                    } else {
                        Telegram::sendMessage(['chat_id' => $chatId, 'text' => "Queue is empty and no recommendations found."]);
                    }
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
