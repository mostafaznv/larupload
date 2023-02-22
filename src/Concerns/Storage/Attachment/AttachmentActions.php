<?php

namespace Mostafaznv\Larupload\Concerns\Storage\Attachment;


use Illuminate\Http\UploadedFile;

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
        if (($this->fileIsSetAndHasValue($file) or $file == LARUPLOAD_NULL) and ($this->fileIsSetAndHasValue($cover) or $cover == null)) {
            $this->file = $file;
            $this->uploaded = false;

            if ($file != LARUPLOAD_NULL) {
                $this->cover = $cover;
                $this->type = $this->getFileType($file);
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
