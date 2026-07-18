<?php

namespace Mostafaznv\Larupload\Concerns;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Mostafaznv\Larupload\Models\LaruploadFFMpegQueue;
use Mostafaznv\Larupload\Models\LaruploadMediaDetailsQueue;


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
            ->orderByDesc('id');
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
            ->orderByDesc('id');
    }


    /**
     * Retrieve latest status log for media details queue process
     *
     * @return HasOne
     */
    public function laruploadMediaDetailsQueue(): HasOne
    {
        return $this->hasOne(LaruploadMediaDetailsQueue::class, 'record_id')
            ->where('record_class', self::class)
            ->orderByDesc('id');
    }

    /**
     * Retrieve all status logs for media details queue process
     *
     * @return HasMany
     */
    public function laruploadMediaDetailsQueues(): HasMany
    {
        return $this->hasMany(LaruploadMediaDetailsQueue::class, 'record_id')
            ->where('record_class', self::class)
            ->orderByDesc('id');
    }
}
