<?php

namespace Mostafaznv\Larupload\Concerns;

use Illuminate\Database\Eloquent\SoftDeletingScope;
use Mostafaznv\Larupload\LaruploadEnum;

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

        static::saved(function($model) {
            $shouldSave = false;

            foreach ($model->attachments as $attachment) {
                if (!$attachment->isUploaded()) {
                    $shouldSave = true;

                    $model = $attachment->saved($model);
                }
            }

            if ($shouldSave) {
                $model->save();
            }
        });

        static::deleted(function($model) {
            if (!$model->hasGlobalScope(SoftDeletingScope::class) or $model->isForceDeleting()) {
                foreach ($model->attachments as $attachment) {
                    $attachment->deleted($model);
                }
            }
        });

        static::retrieved(function($model) {
            foreach ($model->attachments as $attachment) {
                $attachment->setOutput($model);
            }
        });
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
