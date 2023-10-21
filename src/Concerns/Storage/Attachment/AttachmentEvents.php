<?php

namespace Mostafaznv\Larupload\Concerns\Storage\Attachment;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Mostafaznv\Larupload\Actions\GenerateFileIdAction;

trait AttachmentEvents
{
    public function saved(Model $model): Model
    {
        $this->id = GenerateFileIdAction::make($model, $this->secureIdsMethod, $this->mode, $this->name)->run();
        $this->uploaded = true;

        if (isset($this->file)) {
            if ($this->file == LARUPLOAD_NULL) {
                $this->clean($this->id);
            }
            else {
                if (!$this->keepOldFiles) {
                    $this->clean($this->id);
                }

                $this->setBasicDetails();
                $this->setMediaDetails();
                $this->uploadOriginalFile($this->id);
                $this->setCover($this->id);
                $this->handleStyles($this->id, $model);
            }

            $model = $this->setAttributes($model);
        }
        else if (isset($this->cover)) {
            $this->setCover($this->id);

            $model = $this->setAttributes($model);
        }

        return $model;
    }

    public function deleted(): void
    {
        if (!$this->preserveFiles) {
            Storage::disk($this->disk)->deleteDirectory("$this->folder/$this->id");
        }
    }
}
