<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\DownloadService;
use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Support\Facades\Log;

class DownloadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $chatId;
    protected $videoId;
    protected $downloadService;

    /**
     * Create a new job instance.
     */
    public function __construct(string $chatId, string $videoId)
    {
        $this->chatId = $chatId;
        $this->videoId = $videoId;
    }

    /**
     * Execute the job.
     */
    public function handle(DownloadService $downloadService): void
    {
        $this->downloadService = $downloadService;

        Log::info("Starting download for video: {$this->videoId}");

        try {
            // Notify user download started (optional, maybe too spammy if fast)
            // Telegram::sendMessage([
            //     'chat_id' => $this->chatId,
            //     'text' => 'Downloading track...'
            // ]);

            $filePath = $this->downloadService->download($this->videoId);

            if ($filePath && file_exists($filePath)) {
                Log::info("Download complete: $filePath. Sending to Telegram...");

                Telegram::sendAudio([
                    'chat_id' => $this->chatId,
                    'audio' => \Telegram\Bot\FileUpload\InputFile::create($filePath),
                    'caption' => 'Here is your track!',
                ]);

                // Log to history
                // We resolve HistoryService here.
                $historyService = app(\App\Services\HistoryService::class);
                $historyService->logPlayedTrack($this->chatId, [
                    'track_source_id' => $this->videoId, // We only have ID here
                    'title' => 'Unknown Title', // determined by download or passed in construction
                    'artist' => 'Unknown Artist',
                ]);

                // Cleanup
                unlink($filePath);
            } else {
                Telegram::sendMessage([
                    'chat_id' => $this->chatId,
                    'text' => 'Failed to download the track. Please try again.'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('DownloadJob Error: ' . $e->getMessage());
            Telegram::sendMessage([
                'chat_id' => $this->chatId,
                'text' => 'An error occurred while processing your request.'
            ]);
        }
    }
}
