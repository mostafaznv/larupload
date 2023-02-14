<?php

namespace Mostafaznv\Larupload\Concerns;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Mostafaznv\Larupload\Models\LaruploadFFMpegQueue;

trait LaruploadRelations
{
    /**
     * Retrieve latest status log for ffmpeg queue process
     *
     * @return HasOne
     */
    public function laruploadQueue(): HasOne
    {
        return $this->hasOne(LaruploadFFMpegQueue::class, 'record_id')
            ->where('record_class', self::class)
            ->orderBy('id', 'desc');
    }

    /**
     * Retrieve all status logs for ffmpeg queue process
     *
     * @return HasMany
     */
    public function laruploadQueues(): HasMany
    {
        return $this->hasMany(LaruploadFFMpegQueue::class, 'record_id')
            ->where('record_class', self::class)
            ->orderBy('id', 'desc');
    }
}
