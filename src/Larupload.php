<?php

namespace Mostafaznv\Larupload;

use Mostafaznv\Larupload\Concerns\Standalone\BaseStandaloneLarupload;
use Mostafaznv\Larupload\Concerns\Standalone\BootStandaloneLarupload;
use Mostafaznv\Larupload\Concerns\Standalone\StandaloneLaruploadCallables;
use Mostafaznv\Larupload\Concerns\Standalone\StandaloneLaruploadCover;
use Mostafaznv\Larupload\Concerns\Standalone\StandaloneLaruploadNotCallables;
use Mostafaznv\Larupload\Storage\Attachment;

class Larupload extends Attachment
{
    public const ORIGINAL_FOLDER = 'original';
    public const COVER_FOLDER    = 'cover';
    public const STREAM_FOLDER   = 'stream';

    public const LOCAL_DRIVER       = 'local';
    public const FFMPEG_QUEUE_TABLE = 'larupload_ffmpeg_queue';

    use BootStandaloneLarupload;
    use BaseStandaloneLarupload;
    use StandaloneLaruploadCover;
    use StandaloneLaruploadCallables;
    use StandaloneLaruploadNotCallables;
}

