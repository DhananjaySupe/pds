<?php namespace App\Libraries;

require APPPATH.'/ThirdParty/vendor/autoload.php';
use FFMpeg\FFMpeg;
use FFMpeg\Format\Audio\Wav;

class AudioProcessor
{
    protected $ffmpeg;

    public function __construct()
    {
        $this->ffmpeg = FFMpeg::create(); // Uses system FFmpeg
    }

    /**
     * Convert audio file to clean WAV format (16kHz mono)
     *
     * @param string $inputPath
     * @param string $outputPath
     * @return bool
     */
    public function convertToCleanWav(string $inputPath, string $outputPath): bool
    {
        try {
            $audio = $this->ffmpeg->open($inputPath);

            $format = new Wav();
            $format->setAudioChannels(1); // mono
            $format->setAdditionalParameters(['-ar', '16000']); // 16kHz

            $audio->save($format, $outputPath);

            return file_exists($outputPath);
        } catch (\Throwable $e) {
            log_message('error', 'FFmpeg audio conversion failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get Base64-encoded string from audio file
     *
     * @param string $filePath
     * @return string|null
     */
    public function getBase64Audio(string $filePath): ?string
    {
        if (!file_exists($filePath)) {
            return null;
        }

        $content = file_get_contents($filePath);
        $mime = mime_content_type($filePath);

        return 'data:' . $mime . ';base64,' . base64_encode($content);
    }
}
