<?php

namespace Mostafaznv\Larupload\Concerns;

use stdClass;
use Mostafaznv\Larupload\Storage\Attachment;

trait LaruploadTransformers
{
    /**
     * Handle the dynamic setting of attachment objects
     *
     * @param string $key
     * @param $value
     * @return void
     */
    public function setAttribute($key, $value): void
    {
        if ($attachment = $this->getAttachment($key)) {
            $attachment->attach($value);
        }
        else {
            parent::setAttribute($key, $value);
        }
    }

    /**
     * Handle the dynamic retrieval of attachment objects
     *
     * @param string $key
     * @return mixed|null
     */
    public function getAttribute($key): mixed
    {
        if ($attachment = $this->getAttachment($key)) {
            return $attachment;
        }

        return parent::getAttribute($key);
    }

    /**
     * Get All styles (original, cover and ...) of entities for this model
     *
     * @param string|null $name
     * @return object|null
     */
    public function getAttachments(string $name = null): object|null
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

    /**
     * Retrieve attachment if exists, otherwise return null
     *
     * @param $name
     * @return Attachment|null
     */
    protected function getAttachment($name): ?Attachment
    {
        foreach ($this->attachments as $attachment) {
            if ($attachment->getName() == $name) {
                return $attachment;
            }
        }

        return null;
    }

    /**
     * Override toArray method
     *
     * @return array
     */
    public function toArray(): array
    {
        $array = $this->hideLaruploadColumns(parent::toArray());

        // attach attachment entities to array/json response
        foreach ($this->getAttachments() as $name => $attachment) {
            $array[$name] = $attachment;
        }

        return $array;
    }

    private function hideLaruploadColumns(array $array): array
    {
        if ($this->hideLaruploadColumns) {
            foreach ($this->attachments as $attachment) {
                $name = $attachment->getName();

                unset($array["{$name}_file_name"]);

                if ($attachment->getMode() == LaruploadEnum::HEAVY_MODE) {
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
