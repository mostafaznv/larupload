<?php

namespace Mostafaznv\Larupload\Storage\Proxy;

use Illuminate\Http\UploadedFile;
use Mostafaznv\Larupload\Storage\Attachment;


class AttachmentCover
{
    public function __construct(private readonly Attachment $attachment) {}


    public function update(UploadedFile $file): bool
    {
        return $this->attachment->updateCover($file);
    }

    public function detach(): bool
    {
        return $this->attachment->detachCover();
    }
}
