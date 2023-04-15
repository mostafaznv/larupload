<?php

namespace Mostafaznv\Larupload\Concerns\Storage\UploadEntity;


use Illuminate\Http\UploadedFile;
use Mostafaznv\Larupload\Storage\FFMpeg\FFMpeg;

trait FFMpegUploadEntity
{
    /**
     * FFMpeg instance
     */
    protected FFMpeg $ffmpeg;

    /**
     * Specify whether the FFMPEG process should run through the queue or not.
     */
    protected bool $ffmpegQueue;

    /**
     * Specify max FFMPEG processes should run at the same time.
     */
    protected int $ffmpegMaxQueueNum;


    protected function ffmpeg(UploadedFile $file = null): FFMpeg
    {
        if (!isset($this->ffmpeg) or $file) {
            $this->ffmpeg = new FFMpeg($this->file, $this->disk, $this->dominantColorQuality);
        }

        return $this->ffmpeg;
    }
}
