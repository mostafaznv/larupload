<?php

namespace Mostafaznv\Larupload\Traits;

use FFMpeg\Media\Audio;
use FFMpeg\Media\Video;
use Mostafaznv\Larupload\DTOs\Style\AudioStyle;
use Mostafaznv\Larupload\DTOs\Style\VideoStyle;
use Mostafaznv\Larupload\Storage\FFMpeg\FFMpeg;


trait HasVideo
{
    protected FFMpeg $ffmpeg;


    protected function ffmpeg(AudioStyle|VideoStyle|null $style = null): FFMpeg
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


        if (!isset($this->ffmpeg) or $force) {
            $this->ffmpeg = new FFMpeg($this->attachment->file, $this->attachment->disk, $this->attachment->dominantColorQuality);
        }

        return $this->ffmpeg;
    }
}
