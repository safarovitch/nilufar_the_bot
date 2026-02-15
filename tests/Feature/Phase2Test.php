<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\PlayedTrack;
use App\Services\MusicSearchService;
use App\Services\DownloadService;
use App\Jobs\DownloadJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Telegram\Bot\Laravel\Facades\Telegram;
use Mockery;

class Phase2Test extends TestCase
{
    use RefreshDatabase;

    public function test_music_search_service_parsing()
    {
        $service = new MusicSearchService();
        $this->assertTrue(method_exists($service, 'search'));
    }

    public function test_download_job_logs_to_history()
    {
        // 1. Mock DownloadService
        $downloadService = Mockery::mock(DownloadService::class);
        $downloadService->shouldReceive('download')
            ->once()
            ->andReturn('/tmp/test_track.mp3');

        // Create a dummy file to simulate download success
        file_put_contents('/tmp/test_track.mp3', 'dummy audio content');

        // 2. Mock Telegram Facade (Bypass final class BotsManager)
        // Bind to 'telegram' or 'bots' depending on what the Facade uses. 
        // irazasyed/telegram-bot-sdk usually binds 'telegram' or 'bots'.
        // Let's create a fake that intercepts calls.
        $fakeTelegram = new class {
            public function sendAudio($args)
            {
                return true;
            }
            public function answerCallbackQuery($args)
            {
                return true;
            }
            public function sendMessage($args)
            {
                return true;
            }
            // Handle other calls if needed
            public function __call($name, $arguments)
            {
                return true;
            }
        };

        $this->app->bind('telegram', fn() => $fakeTelegram);
        $this->app->bind('bots', fn() => $fakeTelegram);
        $this->app->bind(\Telegram\Bot\BotsManager::class, fn() => $fakeTelegram); // Just in case injectable usage

        // 3. Dispatch Job
        $job = new DownloadJob('12345', 'video_id_123');
        $job->handle($downloadService);

        // 4. Assert DB
        $this->assertDatabaseHas('played_tracks', [
            'track_source_id' => 'video_id_123',
            'user_id' => 12345, // '12345' string cast to int by logPlayedTrack usage of is_numeric
            'title' => 'Unknown Title',
        ]);

        // Cleanup
        if (file_exists('/tmp/test_track.mp3')) {
            unlink('/tmp/test_track.mp3');
        }
    }
}
