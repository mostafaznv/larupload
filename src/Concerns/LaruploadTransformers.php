<?php

namespace Mostafaznv\Larupload\Concerns;

use Mostafaznv\Larupload\Enums\LaruploadMode;
use Mostafaznv\Larupload\Storage\Proxy\AttachmentProxy;
use stdClass;
use Mostafaznv\Larupload\Storage\Attachment;


trait LaruploadTransformers
{
    public function setAttribute(string $key, mixed $value): void
    {
        if ($attachment = $this->getAttachment($key)) {
            $attachment->attach($value);
        }
        else {
            parent::setAttribute($key, $value);
        }
    }

    public function getAttribute(string $key): mixed
    {
        if ($attachment = $this->getAttachment($key)) {
            return new AttachmentProxy($attachment);
        }

        return parent::getAttribute($key);
    }

    public function getAttachments(?string $name = null): object|null
    {
        if ($name) {
            if ($attachment = $this->getAttachment($name)) {
                return $attachment->urls();
            }

            return null;
        }
        else {
            $attachments = new stdClass();

            foreach ($this->attachments as $attachment) {
                $attachments->{$attachment->getName()} = $attachment->urls();
            }

            return $attachments;
        }
    }

    public function attachment(string $name): ?AttachmentProxy
    {
        if ($attachment = $this->getAttachment($name)) {
            return new AttachmentProxy($attachment);
        }

        return null;
    }

    public function toArray(): array
    {
        $array = $this->hideLaruploadColumns(parent::toArray());

        # attach attachment entities to array/json response
        foreach ($this->getAttachments() as $name => $attachment) {
            $array[$name] = $attachment;
        }

        return $array;
    }

    private function getAttachment(string $name): ?Attachment
    {
        foreach ($this->attachments as $attachment) {
            if ($attachment->getName() == $name) {
                return $attachment;
            }
        }

        return null;
    }

    private function hideLaruploadColumns(array $array): array
    {
        if ($this->hideLaruploadColumns) {
            foreach ($this->attachments as $attachment) {
                $name = $attachment->getName();

                unset($array["{$name}_file_name"]);

                if ($attachment->mode === LaruploadMode::HEAVY) {
                    unset($array["{$name}_file_id"]);
                    unset($array["{$name}_file_original_name"]);
                    unset($array["{$name}_file_size"]);
                    unset($array["{$name}_file_type"]);
                    unset($array["{$name}_file_mime_type"]);
                    unset($array["{$name}_file_width"]);
                    unset($array["{$name}_file_height"]);
                    unset($array["{$name}_file_duration"]);
                    unset($array["{$name}_file_dominant_color"]);
                    unset($array["{$name}_file_format"]);
                    unset($array["{$name}_file_cover"]);
                }
                else {
                    unset($array["{$name}_file_meta"]);
                }
            }
        }

        return $array;
    }
}
