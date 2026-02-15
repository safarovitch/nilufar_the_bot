<?php

namespace App\Telegram\Commands;

use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;
use App\Services\MusicSearchService;
use Illuminate\Support\Facades\Log;

class SearchCommand extends Command
{
    protected string $name = 'search';
    protected string $pattern = '{query}';
    protected string $description = 'Search for music (e.g., /search faded)';

    protected $musicSearchService;

    public function __construct(MusicSearchService $musicSearchService)
    {
        $this->musicSearchService = $musicSearchService;
    }

    public function handle()
    {
        // Get all arguments as a single string
        $update = $this->getUpdate();
        $message = $update->getMessage();
        $text = $message->getText();

        // Remove the command (/search) from the text
        $query = trim(str_replace('/search', '', $text));

        Log::info("SearchCommand triggered by user {$message->getFrom()->getId()} with query: '{$query}'");

        if (empty($query)) {
            $this->replyWithMessage([
                'text' => 'Please provide a search query. Example: /search faded'
            ]);
            return;
        }

        $this->replyWithMessage([
            'text' => "Searching for '$query'..."
        ]);

        try {
            // Since commands are constructed by the SDK without DI in some versions, 
            // we might need to resolve the service manually if DI doesn't work out of the box in the SDK's command factory.
            // However, Laravel's container usually handles this if registered correctly.
            // If the SDK instantiates commands directly, we might need `app(MusicSearchService::class)`.
            // Let's assume DI works or fallback.
            $service = app(MusicSearchService::class);
            $results = $service->search($query);

            if (empty($results)) {
                $this->replyWithMessage([
                    'text' => 'No results found.'
                ]);
                return;
            }

            $keyboard = Keyboard::make()->inline();

            foreach ($results as $result) {
                // Callback data: type:video_id
                // d: download immediately
                // q: add to queue
                $downloadData = 'd:' . $result['id'];
                $queueData = 'q:' . $result['id'];

                $buttonText = $result['title'] . ' - ' . $result['uploader'];

                $keyboard->row([
                    Keyboard::inlineButton([
                        'text' => '▶️ ' . $buttonText, // Play icon
                        'callback_data' => $downloadData
                    ])
                ]);

                $keyboard->row([
                    Keyboard::inlineButton([
                        'text' => '➕ Add to Queue',
                        'callback_data' => $queueData
                    ])
                ]);
            }

            $this->replyWithMessage([
                'text' => 'Select a track to download:',
                'reply_markup' => $keyboard
            ]);
        } catch (\Exception $e) {
            Log::error('SearchCommand Error: ' . $e->getMessage());
            $this->replyWithMessage([
                'text' => 'An error occurred while searching. Please try again later.'
            ]);
        }
    }
}
