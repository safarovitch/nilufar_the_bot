<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Storage;

class DownloadService
{
    protected $ytDlpPath;

    public function __construct()
    {
        $this->ytDlpPath = base_path('storage/bin/yt-dlp');
    }

    /**
     * Download audio from a given URL or ID.
     *
     * @param string $id YouTube Video ID
     * @return string|null Path to the downloaded file
     */
    public function download(string $idOrUrl): ?string
    {
        // Detect if input is already a URL (Yandex) or a YouTube ID
        if (filter_var($idOrUrl, FILTER_VALIDATE_URL)) {
            $url = $idOrUrl;
            // Use a hash of the URL or extract ID for filename to avoid filesystem issues
            $fileId = md5($url);
        } elseif (str_starts_with($idOrUrl, 'ya:')) {
            // Handle composite Yandex ID: ya:albumId:trackId
            $parts = explode(':', $idOrUrl);
            if (count($parts) === 3) {
                $albumId = $parts[1];
                $trackId = $parts[2];
                $url = "https://music.yandex.ru/album/{$albumId}/track/{$trackId}";
                // Use the input ID as fileId (sanitized) or hash it
                $fileId = "yandex_{$trackId}";
            } else {
                Log::error("Invalid Yandex ID format: $idOrUrl");
                return null;
            }
        } else {
            $url = "https://www.youtube.com/watch?v=$idOrUrl";
            $fileId = $idOrUrl;
        }

        // Define output path pattern.
        // We'll store in storage/app/music
        $outputDir = storage_path('app/music');
        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        // Output template: music/id.ext
        $outputTemplate = "$outputDir/%(id)s.%(ext)s";

        $command = [
            $this->ytDlpPath,
            '-x', // Extract audio
            '--audio-format',
            'mp3',
            '--audio-quality',
            '192K',
            '-o',
            $outputTemplate,
            '--no-playlist',
            '--js-runtimes',
            'node',
            $url
        ];

        // Add cookies if available
        $storageCookiesPath = storage_path('app/cookies.txt');
        $rootCookiesPath = base_path('cookies.txt');

        $cookiesPath = null;
        if (file_exists($storageCookiesPath)) {
            $cookiesPath = $storageCookiesPath;
        } elseif (file_exists($rootCookiesPath)) {
            $cookiesPath = $rootCookiesPath;
        }

        if ($cookiesPath) {
            Log::info("DownloadService: Cookies found at $cookiesPath");
            array_splice($command, 1, 0, ['--cookies', $cookiesPath]);
        } else {
            Log::warning("DownloadService: Cookies file NOT found at $storageCookiesPath or $rootCookiesPath");
        }

        // Add User Agent
        array_splice($command, 1, 0, ['--user-agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36']);

        try {
            $process = new Process($command);
            $process->setTimeout(300); // 5 minutes max download
            $process->run();

            if (!$process->isSuccessful()) {
                Log::error('yt-dlp download failed', ['output' => $process->getErrorOutput()]);
                return null;
            }

            // Path to the downloaded file (assuming mp3 format)
            $filePath = "$outputDir/$fileId.mp3";

            if (file_exists($filePath)) {
                return $filePath;
            }

            Log::error("Download succeeded but file not found at $filePath");
            return null;
        } catch (\Exception $e) {
            Log::error('DownloadService Error: ' . $e->getMessage());
            return null;
        }
    }
}
