<?php

namespace Mostafaznv\Larupload\Concerns\Storage\UploadEntity;


use FFMpeg\Media\Audio;
use FFMpeg\Media\Video;
use Illuminate\Http\UploadedFile;
use Mostafaznv\Larupload\DTOs\Style\AudioStyle;
use Mostafaznv\Larupload\DTOs\Style\Style;
use Mostafaznv\Larupload\DTOs\Style\VideoStyle;
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


    protected function ffmpeg(UploadedFile $file = null, AudioStyle|VideoStyle|null $style = null): FFMpeg
    {
        $force = false;

        if ($style and isset($this->ffmpeg)) {
            $media = $this->ffmpeg->getMedia();

            // @codeCoverageIgnoreStart
            // it's an unlikely scenario, however, we must consider it
            if ($style instanceof AudioStyle and $media instanceof Video) {
                $force = true;
            }
            // @codeCoverageIgnoreEnd

            if ($style instanceof VideoStyle and $media instanceof Audio) {
                $force = true;
            }
        }


        if (!isset($this->ffmpeg) or $file or $force) {
            $this->ffmpeg = new FFMpeg($this->file, $this->disk, $this->dominantColorQuality);
        }

        return $this->ffmpeg;
    }
}
