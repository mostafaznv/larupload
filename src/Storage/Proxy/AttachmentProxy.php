<?php

namespace Mostafaznv\Larupload\Storage\Proxy;

use Illuminate\Http\UploadedFile;
use Mostafaznv\Larupload\Larupload;
use Illuminate\Http\RedirectResponse;
use Mostafaznv\Larupload\Storage\Attachment;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentProxy
{
    public function __construct(private readonly Attachment $attachment) {}

    public function attach(mixed $file, ?UploadedFile $cover = null): bool
    {
        return $this->attachment->attach($file, $cover);
    }

    public function detach(): bool
    {
        return $this->attachment->detach();
    }

    public function cover(): AttachmentCover
    {
        return new AttachmentCover($this->attachment);
    }

    public function meta(string $key = null): object|int|string|null
    {
        return $this->attachment->meta($key);
    }

    public function urls(): object
    {
        return $this->attachment->urls();
    }

    public function url(string $style = Larupload::ORIGINAL_FOLDER): ?string
    {
        return $this->attachment->url($style);
    }

    public function download(string $style = Larupload::ORIGINAL_FOLDER): StreamedResponse|RedirectResponse|null
    {
        return $this->attachment->download($style);
    }

    public function handleFFMpegQueue(bool $isLastOne = false): void
    {
        $this->attachment->handleFFMpegQueue($isLastOne);
    }
}
