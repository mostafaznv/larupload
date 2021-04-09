<?php

namespace Mostafaznv\Larupload\Models;

use Illuminate\Database\Eloquent\Model;
use Mostafaznv\Larupload\LaruploadEnum;

class LaruploadFFMpegQueue extends Model
{
    protected $table = LaruploadEnum::FFMPEG_QUEUE_TABLE;
}
