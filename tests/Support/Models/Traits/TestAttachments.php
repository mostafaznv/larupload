<?php

namespace Mostafaznv\Larupload\Test\Support\Models\Traits;

use Mostafaznv\Larupload\Test\Support\TestAttachmentBuilder;

trait TestAttachments
{
    public function attachments(): array
    {
        return TestAttachmentBuilder::make($this->mode)->toArray();
    }

    public function setAttachments(array $attachments): array
    {
        $this->attachments = $attachments;

        $table = $this->getTable();

        foreach ($this->attachments as $attachment) {
            $attachment->folder($table);
        }

        return $this->attachments;
    }

    public function withAllAttachments(): array
    {
        return $this->setAttachments(
            TestAttachmentBuilder::make($this->mode)->withAll()->toArray()
        );
    }

    public function withAllImages(): array
    {
        return $this->setAttachments(
            TestAttachmentBuilder::make($this->mode)->withAllImages()->toArray()
        );
    }

    public function withAllVideos(): array
    {
        return $this->setAttachments(
            TestAttachmentBuilder::make($this->mode)->withAllVideos()->toArray()
        );
    }

    public function withAllAudios(): array
    {
        return $this->setAttachments(
            TestAttachmentBuilder::make($this->mode)->withAllAudios()->toArray()
        );
    }

    public function withStreams(): array
    {
        return $this->setAttachments(
            TestAttachmentBuilder::make($this->mode)->withStreams()->toArray()
        );
    }

    public function withAllVideosAndStreams(): array
    {
        return $this->setAttachments(
            TestAttachmentBuilder::make($this->mode)->withAllVideosAndStreams()->toArray()
        );
    }

    public function clearAttachments(): array
    {
        return $this->setAttachments(
            TestAttachmentBuilder::make($this->mode)->toArray()
        );
    }
}
