<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class MusicSearchService
{
    protected $ytDlpPath;

    public function __construct()
    {
        $this->ytDlpPath = base_path('storage/bin/yt-dlp');
    }

    /**
     * Search for music using external providers (e.g., YouTube).
     *
     * @param string $query
     * @return array
     */
    public function search(string $query): array
    {
        // ytsearch5: limits to 5 results
        // --dump-json: outputs JSON for each result (line by line)
        // --flat-playlist: faster, doesn't resolve every video detail if not needed, 
        // but for search results we usually want title/duration. 
        // flat-playlist with dump-json on search might return limited info.
        // Let's try without flat-playlist or with check. 
        // Actually for "ytsearchN:", it returns a playlist structure if we use dump-single-json,
        // or multiple json objects if we use dump-json.
        // safer: --dump-json --no-playlist --flat-playlist is for playlists. 
        // For search, we want the video entries.

        $command = [
            $this->ytDlpPath,
            "ytsearch5:$query",
            '--dump-json',
            '--ignore-errors', // Skip errors
            '--js-runtimes', // Explicitly use node
            'node',
        ];

        // Add cookies if available
        $cookiesPath = storage_path('app/cookies.txt');
        if (file_exists($cookiesPath)) {
            Log::info("MusicSearchService: Cookies found at $cookiesPath");
            array_splice($command, 1, 0, ['--cookies', $cookiesPath]);
        } else {
            Log::warning("MusicSearchService: Cookies file NOT found at $cookiesPath");
        }

        // Add User Agent
        array_splice($command, 1, 0, ['--user-agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36']);

        Log::info('MusicSearchService executing command: ' . implode(' ', $command));

        try {
            $process = new Process($command);
            $process->setTimeout(60);
            $process->run();

            if (!$process->isSuccessful()) {
                Log::error('yt-dlp search failed', ['output' => $process->getErrorOutput()]);
                return [];
            }

            $output = $process->getOutput();
            $results = [];

            // yt-dlp outputs one JSON object per line for multiple results
            $lines = explode("\n", trim($output));

            foreach ($lines as $line) {
                $data = json_decode($line, true);
                if ($data) {
                    $results[] = [
                        'id' => $data['id'] ?? null,
                        'title' => $data['title'] ?? 'Unknown Title',
                        'duration' => $data['duration'] ?? 0,
                        'uploader' => $data['uploader'] ?? 'Unknown Artist',
                        'webpage_url' => $data['webpage_url'] ?? null,
                    ];
                }
            }

            return $results;
        } catch (\Exception $e) {
            Log::error('MusicSearchService Error: ' . $e->getMessage());
            return [];
        }
    }
}
