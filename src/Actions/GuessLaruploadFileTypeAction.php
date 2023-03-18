<?php

namespace Mostafaznv\Larupload\Actions;

use Illuminate\Http\UploadedFile;
use Mostafaznv\Larupload\Enums\LaruploadFileType;

class GuessLaruploadFileTypeAction
{
    public function __construct(private readonly UploadedFile $file) {}

    public static function make(UploadedFile $file): self
    {
        return new self($file);
    }


    public function calc(): ?LaruploadFileType
    {
        if ($this->file->isValid()) {
            $mime = $this->file->getMimeType();

            return $this->mimeToType($mime);
        }

        return null;
    }

    public function isImage(): bool
    {
        return $this->mimeToType($this->file->getMimeType()) === LaruploadFileType::IMAGE;
    }

    private function mimeToType(string $mime = null): LaruploadFileType
    {
        if (str_contains($mime, 'image/')) {
            return LaruploadFileType::IMAGE;
        }
        else if (str_contains($mime, 'video/')) {
            return LaruploadFileType::VIDEO;
        }
        else if (str_contains($mime, 'audio/')) {
            return LaruploadFileType::AUDIO;
        }
        else if ($mime == 'application/pdf') {
            return LaruploadFileType::PDF;
        }
        else if ($mime == 'application/zip' or $mime == 'application/x-rar-compressed') {
            return LaruploadFileType::COMPRESSED;
        }

        return LaruploadFileType::FILE;
    }
}
