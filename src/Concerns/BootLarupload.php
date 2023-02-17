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
}
