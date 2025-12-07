<?php

namespace Mostafaznv\Larupload\Actions\Attachment;

use Illuminate\Database\Eloquent\Model;
use Mostafaznv\Larupload\Actions\GenerateFileIdAction;


class SaveAttachmentAction extends StoreAttachmentAction
{
    public function execute(Model $model): Model
    {
        $this->attachment->id = GenerateFileIdAction::make($model, $this->attachment->secureIdsMethod, $this->attachment->mode, $this->attachment->name)->run();
        $this->attachment->uploaded = true;

        if (isset($this->attachment->file)) {
            if ($this->attachment->file === false) {
                $this->clean();
            }
            else {
                $this->attachment->shouldProcessStyles = true;

                if (!$this->attachment->keepOldFiles) {
                    $this->clean();
                }

                $this->basic();
                $this->media();
                $this->uploadOriginalFile($this->attachment->id);
                $this->setCover($this->attachment->id);
            }

            $model = $this->setAttributes($model);
        }

        if (isset($this->attachment->cover)) {
            $this->setCover($this->attachment->id);

            $model = $this->setAttributes($model);
        }


        return $model;
    }
}
