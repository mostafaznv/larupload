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
        else if ($this->isDocument($mime)) {
            return LaruploadFileType::DOCUMENT;
        }
        else if ($this->isCompressed($mime)) {
            return LaruploadFileType::COMPRESSED;
        }

        return LaruploadFileType::FILE;
    }

    private function isDocument(string $mime): bool {
        $mimeTypes = [
            // pdf, epub, csv, tsv, txt
            'application/pdf', 'application/epub+zip', 'text/csv', 'text/tab-separated-values', 'text/plain',
            // doc, docx
            'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            // ppt, pptx
            'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            // xls, xlsx
            'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            // odt, ods, odp
            'application/vnd.oasis.opendocument.spreadsheet', 'application/vnd.oasis.opendocument.presentation', 'application/vnd.oasis.opendocument.text',
            // rtf
            'application/rtf',
            // html, xml
            'application/xhtml+xml', 'text/html', 'text/xml',
        ];

        return in_array($mime, $mimeTypes);
    }

    private function isCompressed(string $mime): bool {
        $mimeTypes = [
            'application/zip', 'application/x-rar-compressed', 'application/x-7z-compressed', 'application/x-tar',
            'application/gzip', 'application/x-bzip2', 'application/x-xz', 'application/x-lzip', 'application/x-lz4',
            'application/zstd', 'application/x-compress', 'application/gzip', 'application/x-bzip-compressed-tar',
            'application/x-bzip2-compressed-tar'
        ];

        return in_array($mime, $mimeTypes);
    }
}
