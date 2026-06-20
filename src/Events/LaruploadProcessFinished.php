<?php

namespace Mostafaznv\Larupload\Events;

use Illuminate\Queue\SerializesModels;


class LaruploadProcessFinished
{
    use SerializesModels;

    public string    $id;
    public string $model;


    public function __construct(int|string $id, string $model)
    {
        $this->id = $id;
        $this->model = $model;
    }
}
