<?php

namespace Mostafaznv\Larupload\Traits;

use stdClass;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Mostafaznv\Larupload\Models\LaruploadFFMpegQueue;

trait Larupload
{
    protected array $attachments = [];

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
            foreach ($this->attachments as $name => $attachment) {
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
