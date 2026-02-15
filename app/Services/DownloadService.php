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
    public function download(string $id): ?string
    {
        $url = "https://www.youtube.com/watch?v=$id";

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
            $url
        ];

        try {
            $process = new Process($command);
            $process->setTimeout(300); // 5 minutes max download
            $process->run();

            if (!$process->isSuccessful()) {
                Log::error('yt-dlp download failed', ['output' => $process->getErrorOutput()]);
                return null;
            }

            // Path to the downloaded file (assuming mp3 format)
            $filePath = "$outputDir/$id.mp3";

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
