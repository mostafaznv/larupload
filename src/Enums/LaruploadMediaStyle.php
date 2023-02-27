<?php

namespace Mostafaznv\Larupload\Enums;

use FFMpeg\Filters\Video\ResizeFilter;

enum LaruploadMediaStyle
{
    /**
     * fits to the dimensions, might introduce anamorphosis
     * old name: EXACT
     */
    case FIT;

    /**
     * resizes the media inside the given dimension, no anamorphosis
     */
    case AUTO;

    /**
     * resizes the video to fit the dimension width, no anamorphosis
     * old name: PORTRAIT
     */
    case SCALE_WIDTH;

    /**
     * resizes the video to fit the dimension height, no anamorphosis
     * old name: LANDSCAPE
     */
    case SCALE_HEIGHT;

    /**
     * scale/crop the media with the exact dimension, no anamorphosis
     */
    case CROP;


    public function ffmpegResizeFilter(): ?string
    {
        return match ($this) {
            self::FIT          => ResizeFilter::RESIZEMODE_FIT,
            self::AUTO         => ResizeFilter::RESIZEMODE_INSET,
            self::SCALE_WIDTH  => ResizeFilter::RESIZEMODE_SCALE_WIDTH,
            self::SCALE_HEIGHT => ResizeFilter::RESIZEMODE_SCALE_HEIGHT,
            self::CROP         => null,
        };
    }
}
