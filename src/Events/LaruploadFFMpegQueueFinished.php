<?php

namespace Mostafaznv\Larupload\Events;

use Illuminate\Queue\SerializesModels;

class LaruploadFFMpegQueueFinished
{
    use SerializesModels;

    public $id;
    public $model;
    public $statusId;

    public function __construct($id, $model, $statusId)
    {
        $this->id = $id;
        $this->model = $model;
        $this->statusId = $statusId;
    }
}
