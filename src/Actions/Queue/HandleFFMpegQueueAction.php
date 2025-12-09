<?php

namespace Mostafaznv\Larupload\Actions\Queue;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mostafaznv\Larupload\Actions\GuessLaruploadFileTypeAction;
use Mostafaznv\Larupload\Actions\HandleStylesAction;
use Mostafaznv\Larupload\Enums\LaruploadFileType;
use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Storage\Attachment;


class HandleFFMpegQueueAction
{
    public function execute(Attachment $attachment, bool $isLastOne = false, bool $standalone = false): void
    {
        $attachment->file = $this->prepareFileForFFMpegProcess($attachment);
        $attachment->type = GuessLaruploadFileTypeAction::make($attachment->file)->calc();


        if ($attachment->type == LaruploadFileType::VIDEO) {
            HandleStylesAction::make($attachment)->handleVideoStyles($attachment->id);

        }
        else if ($attachment->type == LaruploadFileType::AUDIO) {
            HandleStylesAction::make($attachment)->handleAudioStyles($attachment->id);
        }


        if ($this->shouldDeleteLocalDirectory($attachment, $isLastOne)) {
            Storage::disk($attachment->localDisk)->deleteDirectory(
                $standalone ? "$attachment->folder/$attachment->nameKebab" : "$attachment->folder/$attachment->id"
            );
        }
    }

    private function prepareFileForFFMpegProcess(Attachment $attachment): UploadedFile
    {
        $basePath = larupload_relative_path($attachment, $attachment->id, Larupload::ORIGINAL_FOLDER);
        $path = $basePath . '/' . $attachment->output->name;
        $disk = disk_driver_is_local($attachment->disk) ? $attachment->disk : $attachment->localDisk;

        $path = Storage::disk($disk)->path($path);

        return new UploadedFile($path, $attachment->output->name, null, null, true);
    }

    private function shouldDeleteLocalDirectory(Attachment $attachment, bool $isLastOne): bool
    {
        if (!disk_driver_is_local($attachment->disk)) {
            if ($attachment->secureIdsMethod->hasUnifiedAttachments()) {
                return $isLastOne;
            }

            return true;
        }

        return false;
    }
}
