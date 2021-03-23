<?php

namespace Mostafaznv\Larupload\Traits;

use Mostafaznv\Larupload\LaruploadEnum;
use stdClass;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Mostafaznv\Larupload\Models\LaruploadFFMpegQueue;

trait Larupload
{
    /**
     * Attachment Entities
     *
     * @var array
     */
    protected array $attachments = [];

    protected bool $hideLaruploadColumns;

    /**
     * Holds the hash value for the current LARUPLOAD_NULL constant
     *
     * @var string
     */
    protected static string $laruploadNull;

    /**
     * Uploaded flag to prevent infinite loop
     *
     * @var bool
     */
    protected static bool $uploaded = false;

    /**
     * Initialize attachments
     */
    protected function initializeLarupload()
    {
        $this->hideLaruploadColumns = config('larupload.hide-table-columns');

        $this->attachments = $this->attachments();
        $table = $this->getTable();

        foreach ($this->attachments as $attachment) {
            $attachment->folder($table);
        }
    }

    /**
     * Boot the Larupload trait for the model
     * Register eloquent event handlers
     *
     */
    public static function bootLarupload()
    {
        static::$laruploadNull = crc32(time());

        if (!defined('LARUPLOAD_NULL')) {
            define('LARUPLOAD_NULL', static::$laruploadNull);
        }

        static::saved(function($model) {
            if (!self::$uploaded) {
                self::$uploaded = true;

                foreach ($model->attachments as $attachment) {
                    $model = $attachment->saved($model);
                }

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

    /**
     * Get the entities should upload into the model
     *
     * @return array
     */
    abstract public function attachments(): array;

    /**
     * Handle the dynamic setting of attachment objects
     *
     * @param string $key
     * @param $value
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        if ($attachment = $this->getAttachment($key)) {
            $uploaded = $attachment->setUploadedFile($value);

            if ($uploaded) {
                static::$uploaded = false;
            }
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
    public function getAttribute($key)
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
    public function getAttachments(string $name = null)
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
     * @return mixed|null
     */
    protected function getAttachment($name)
    {
        foreach ($this->attachments as $attachment) {
            if ($attachment->getName() == $name) {
                return $attachment;
            }
        }

        return null;
    }

    /**
     * Hide larupload columns
     *
     * @param array $array
     * @return array
     */
    protected function hideLaruploadColumns(array $array): array
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

    /**
     * Retrieve latest status log for ffmpeg queue process
     *
     * @return HasOne
     */
    public function laruploadQueue(): HasOne
    {
        return $this->hasOne(LaruploadFFMpegQueue::class, 'record_id')->where('record_class', self::class)->orderBy('id', 'desc');
    }

    /**
     * Retrieve all status logs for ffmpeg queue process
     *
     * @return HasMany
     */
    public function laruploadQueues(): HasMany
    {
        return $this->hasMany(LaruploadFFMpegQueue::class, 'record_id')->where('record_class', self::class)->orderBy('id', 'desc');
    }
}
