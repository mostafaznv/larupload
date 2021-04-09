<?php

namespace Mostafaznv\Larupload;

class LaruploadEnum
{
    const HEAVY_MODE      = 'heavy';
    const LIGHT_MODE      = 'light';
    const STANDALONE_MODE = 'standalone';

    const SLUG_NAMING_METHOD      = 'slug';
    const HASH_FILE_NAMING_METHOD = 'hash_file';
    const TIME_NAMING_METHOD      = 'time';

    const GD_IMAGE_LIBRARY      = 'Imagine\Gd\Imagine';
    const IMAGICK_IMAGE_LIBRARY = 'Imagine\Imagick\Imagine';
    const GMAGICK_IMAGE_LIBRARY = 'Imagine\Gmagick\Imagine';

    const LANDSCAPE_STYLE_MODE = 'landscape';
    const PORTRAIT_STYLE_MODE  = 'portrait';
    const CROP_STYLE_MODE      = 'crop';
    const EXACT_STYLE_MODE     = 'exact';
    const AUTO_STYLE_MODE      = 'auto';

    const IMAGE_STYLE_TYPE = 'image';
    const VIDEO_STYLE_TYPE = 'video';

    const IMAGE      = 'image';
    const VIDEO      = 'video';
    const AUDIO      = 'audio';
    const PDF        = 'pdf';
    const COMPRESSED = 'compressed';
    const FILE       = 'file';

    const ORIGINAL_FOLDER = 'original';
    const COVER_FOLDER    = 'cover';
    const STREAM_FOLDER   = 'stream';

    const LOCAL_DRIVER = 'local';
    const FFMPEG_QUEUE_TABLE = 'larupload_ffmpeg_queue';
}
