<?php

namespace Mostafaznv\Larupload\Events;

use Illuminate\Queue\SerializesModels;

class LaruploadFFMpegQueueFinished
{
    use SerializesModels;

    public int    $id;
    public string $model;
    public int    $statusId;

    public function __construct(int $id, string $model, int $statusId)
    {
        $this->id = $id;
        $this->model = $model;
        $this->statusId = $statusId;
    }
}
