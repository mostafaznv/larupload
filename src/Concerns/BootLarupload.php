<?php

namespace Mostafaznv\Larupload\Concerns;

trait BootLarupload
{
    protected static string $laruploadNull;

    private bool $hideLaruploadColumns;

    protected function initializeLarupload(): void
    {
        $this->hideLaruploadColumns = config('larupload.hide-table-columns');

        $this->attachments = $this->attachments();
        $table = $this->getTable();

        foreach ($this->attachments as $attachment) {
            $attachment->folder($table);
        }
    }

    public static function bootLarupload(): void
    {
        static::$laruploadNull = crc32(time());

        if (!defined('LARUPLOAD_NULL')) {
            define('LARUPLOAD_NULL', static::$laruploadNull);
        }
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
