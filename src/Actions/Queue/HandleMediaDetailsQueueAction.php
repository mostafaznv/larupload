<?php

namespace Mostafaznv\Larupload\Actions\Queue;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mostafaznv\Larupload\Enums\LaruploadFileType;
use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Storage\Attachment;
use Mostafaznv\Larupload\Storage\FFMpeg\FFMpeg;
use Mostafaznv\Larupload\Storage\Image;
use RuntimeException;


class HandleMediaDetailsQueueAction
{
    public function execute(Model $model, Attachment $attachment): void
    {
        $attachment->file = $this->file($attachment);


        switch ($attachment->meta('type')) {
            case LaruploadFileType::VIDEO:
            case LaruploadFileType::AUDIO:
                $meta = $this->ffmpeg($attachment)->getMeta();

                $attachment->output->width = $meta->width;
                $attachment->output->height = $meta->height;
                $attachment->output->duration = $meta->duration;


                $model = $attachment->output->save($model, $attachment->name, $attachment->mode);
                $model->saveQuietly();

                break;

            case LaruploadFileType::IMAGE:
                $img = $this->img($attachment);
                $meta = $img->getMeta();

                $attachment->output->width = $meta->width;
                $attachment->output->height = $meta->height;
                $attachment->output->dominantColor = null;

                if ($attachment->dominantColor) {
                    $attachment->output->dominantColor = $img->getDominantColor();
                }

                $model = $attachment->output->save($model, $attachment->name, $attachment->mode);
                $model->saveQuietly();

                break;
        }


        delete_local_copy($attachment);
    }


    private function file(Attachment $attachment): UploadedFile
    {
        $basePath = larupload_relative_path($attachment, $attachment->id, Larupload::ORIGINAL_FOLDER);
        $path = $basePath . '/' . $attachment->output->name;
        $disk = disk_driver_is_local($attachment->disk) ? $attachment->disk : $attachment->localDisk;

        $exists = Storage::disk($disk)->exists($path);

        if ($exists) {
            $path = Storage::disk($disk)->path($path);

            return new UploadedFile($path, $attachment->output->name, null, null, true);
        }

        throw new RuntimeException("File not found on disk: $disk, path: $path");
    }

    private function ffmpeg(Attachment $attachment): FFMpeg
    {
        return new FFMpeg($attachment->file, $attachment->disk, $attachment->dominantColorQuality);
    }

    private function img(Attachment $attachment): Image
    {
        return new Image(
            file: $attachment->file,
            disk: $attachment->disk,
            library: $attachment->imageProcessingLibrary,
            dominantColorQuality: $attachment->dominantColorQuality
        );
    }
}
