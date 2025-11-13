<?php

namespace Mostafaznv\Larupload\Concerns\Storage\Attachment;

use Illuminate\Http\UploadedFile;
use Mostafaznv\Larupload\Actions\GuessLaruploadFileTypeAction;
use Mostafaznv\Larupload\Actions\OptimizeImageAction;
use Mostafaznv\Larupload\Enums\LaruploadFileType;


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
        $fileIsAttachable = (file_has_value($file) or $file == LARUPLOAD_NULL);
        $coverIsAttachable = (file_has_value($cover) or $cover == null);

        if ($fileIsAttachable and $coverIsAttachable) {
            file_is_valid($file, $this->name, 'file');
            file_is_valid($cover, $this->name, 'cover');

            $this->file = $file;
            $this->uploaded = false;

            if ($file != LARUPLOAD_NULL) {
                $this->cover = $cover;
                $this->type = GuessLaruploadFileTypeAction::make($file)->calc();

                if ($this->type === LaruploadFileType::IMAGE && $this->optimizeImage) {
                    $this->file = OptimizeImageAction::make($file)->process();
                }
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
