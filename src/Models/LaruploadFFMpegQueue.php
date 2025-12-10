<?php

namespace Mostafaznv\Larupload\Models;

use Illuminate\Database\Eloquent\Model;
use Mostafaznv\Larupload\Larupload;


class LaruploadFFMpegQueue extends Model
{
    protected $table = Larupload::FFMPEG_QUEUE_TABLE;
}
