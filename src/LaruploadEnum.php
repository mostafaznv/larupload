<?php

namespace Mostafaznv\Larupload;

class LaruploadEnum
{
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
