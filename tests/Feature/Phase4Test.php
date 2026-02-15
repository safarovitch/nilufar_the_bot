<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\QueueService;
use App\Models\PlaybackQueue;
use App\Telegram\Commands\NextCommand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use App\Jobs\DownloadJob;

class Phase4Test extends TestCase
{
    use RefreshDatabase;

    public function test_queue_service_add_and_pop()
    {
        $service = new QueueService();
        $userId = 123;

        // Add 2 tracks
        $service->addToQueue($userId, [
            'track_source_id' => 't1',
            'title' => 'Track 1'
        ]);

        $service->addToQueue($userId, [
            'track_source_id' => 't2',
            'title' => 'Track 2'
        ]);

        $queue = $service->getQueue($userId);
        $this->assertCount(2, $queue);
        $this->assertEquals('Track 1', $queue[0]->title);
        $this->assertEquals('Track 2', $queue[1]->title);

        // Pop first
        $popped = $service->popNextTrack($userId);
        $this->assertEquals('Track 1', $popped->title);

        // Check remaining
        $queue = $service->getQueue($userId);
        $this->assertCount(1, $queue);
        $this->assertEquals('Track 2', $queue[0]->title);
    }

    public function test_next_command_dispatches_download_job()
    {
        Queue::fake();

        // Populate queue
        $service = new QueueService();
        $service->addToQueue(999, ['track_source_id' => 'vid_123', 'title' => 'Queued Track']);

        // Mock Telegram Facade
        $fakeTelegram = new class {
            public function answerCallbackQuery($args) {}
            public function sendMessage($args) {}
            public function commandsHandler($webhook = false)
            {
                return new class {
                    public function has($key)
                    {
                        return true;
                    }
                    public function getCallbackQuery()
                    {
                        return new class {
                            public function getData()
                            {
                                return 'next';
                            }
                            public function getId()
                            {
                                return 'cb_id';
                            }
                            public function getMessage()
                            {
                                return new class {
                                    public function getChat()
                                    {
                                        return new class {
                                            public function getId()
                                            {
                                                return 999;
                                            }
                                        };
                                    }
                                };
                            }
                        };
                    }
                };
            }
        };

        // Bind for all possible resolutions
        $this->app->bind('telegram', fn() => $fakeTelegram);
        $this->app->bind('bots', fn() => $fakeTelegram);
        if (class_exists(\Telegram\Bot\BotsManager::class)) {
            $this->app->bind(\Telegram\Bot\BotsManager::class, fn() => $fakeTelegram);
        }

        // Call the controller route
        $response = $this->postJson('/api/telegram/webhook', [
            'callback_query' => [
                'data' => 'next',
                'message' => ['chat' => ['id' => 999]],
                'id' => 'cb_id'
            ]
        ]);

        $response->assertOk();

        // Assert Job Dispatched
        Queue::assertPushed(DownloadJob::class, function ($job) {
            return $job->videoId === 'vid_123';
        });
    }
}
