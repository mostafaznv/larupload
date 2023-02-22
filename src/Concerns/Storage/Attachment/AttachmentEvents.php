<?php

namespace Mostafaznv\Larupload\Concerns\Storage\Attachment;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

trait AttachmentEvents
{
    public function saved(Model $model): Model
    {
        $this->id = $model->id;
        $this->uploaded = true;

        if (isset($this->file)) {
            if ($this->file == LARUPLOAD_NULL) {
                $this->clean($model->id);
            }
            else {
                if (!$this->keepOldFiles) {
                    $this->clean($model->id);
                }

                $this->setBasicDetails();
                $this->setMediaDetails();
                $this->uploadOriginalFile($model->id);
                $this->setCover($model->id);
                $this->handleStyles($model->id, $model->getMorphClass());
            }

            $model = $this->setAttributes($model);
        }
        else if (isset($this->cover)) {
            $this->setCover($model->id);

            $model = $this->setAttributes($model);
        }

        return $model;
    }

    public function deleted(Model $model): void
    {
        if (!$this->preserveFiles) {
            Storage::disk($this->disk)->deleteDirectory("$this->folder/$model->id");
        }
    }
}
