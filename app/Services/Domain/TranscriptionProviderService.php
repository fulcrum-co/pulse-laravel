<?php

declare(strict_types=1);

namespace App\Services\Domain;

class TranscriptionProviderService
{
    /**
     * Get MIME type from file path.
     */
    public function getMimeType(string $filePath): string
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        return match ($extension) {
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'm4a' => 'audio/mp4',
            'ogg' => 'audio/ogg',
            'webm' => 'audio/webm',
            'flac' => 'audio/flac',
            default => 'audio/mpeg',
        };
    }

    /**
     * Get supported audio formats.
     */
    public function getSupportedFormats(): array
    {
        return ['mp3', 'wav', 'm4a', 'ogg', 'webm', 'flac'];
    }

    /**
     * Validate audio file format.
     */
    public function isValidAudioFile(string $filePath): bool
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        return in_array($extension, $this->getSupportedFormats());
    }
}
