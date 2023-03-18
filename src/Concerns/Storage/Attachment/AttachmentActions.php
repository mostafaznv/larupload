<?php

namespace Mostafaznv\Larupload\Concerns\Storage\Attachment;


use Illuminate\Http\UploadedFile;
use Mostafaznv\Larupload\Actions\GuessLaruploadFileTypeAction;

trait AttachmentActions
{
    /**
     * Attach files into entity
     *
     * @param mixed $file
     * @param UploadedFile|null $cover
     * @return bool
     */
    public function attach(mixed $file, ?UploadedFile $cover = null): bool
    {
        $fileIsAttachable = ($this->fileIsSetAndHasValue($file) or $file == LARUPLOAD_NULL);
        $coverIsAttachable = ($this->fileIsSetAndHasValue($cover) or $cover == null);

        if ($fileIsAttachable and $coverIsAttachable) {
            $this->file = $file;
            $this->uploaded = false;

            if ($file != LARUPLOAD_NULL) {
                $this->cover = $cover;
                $this->type = GuessLaruploadFileTypeAction::make($file)();
            }

            return true;
        }

        return false;
    }

    public function detach(): bool
    {
        return $this->attach(LARUPLOAD_NULL);
    }
}
