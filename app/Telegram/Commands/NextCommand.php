<?php

namespace App\Telegram\Commands;

use Telegram\Bot\Commands\Command;
use App\Services\QueueService;
use App\Services\RecommendationService;
use App\Jobs\DownloadJob;

class NextCommand extends Command
{
    protected string $name = 'next';
    protected string $description = 'Play the next track from queue or recommendations';

    public function handle()
    {
        $chatId = $this->getUpdate()->getMessage()->getChat()->getId();

        $queueService = app(QueueService::class);
        $nextTrack = $queueService->popNextTrack($chatId);

        if ($nextTrack) {
            $this->replyWithMessage(['text' => "Playing next from queue: {$nextTrack->title}"]);
            DownloadJob::dispatch($chatId, $nextTrack->track_source_id);
        } else {
            // Recommendation fallback
            $recService = app(RecommendationService::class);
            $recs = $recService->getRecommendations($chatId);

            if (!empty($recs)) {
                $track = $recs[0];
                $this->replyWithMessage(['text' => "Queue empty. Playing recommendation: {$track['title']}"]);
                DownloadJob::dispatch($chatId, $track['track_source_id']);
            } else {
                $this->replyWithMessage(['text' => "Queue is empty and no recommendations found."]);
            }
        }
    }
}
