<?php

namespace Mostafaznv\Larupload\Actions\Attachment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Mostafaznv\Larupload\Actions\Cover\SetCoverAction;
use Mostafaznv\Larupload\Actions\SetFileNameAction;
use Mostafaznv\Larupload\DTOs\CoverActionData;
use Mostafaznv\Larupload\Enums\LaruploadFileType;
use Mostafaznv\Larupload\Enums\LaruploadMode;
use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Storage\Attachment;
use Mostafaznv\Larupload\Traits\HasImage;
use Mostafaznv\Larupload\Traits\HasVideo;


abstract class StoreAttachmentAction
{
    use HasImage, HasVideo;


    public function __construct(protected Attachment $attachment) {}

    public static function make(Attachment $attachment): static
    {
        return new static($attachment);
    }


    protected function basic(): void
    {
        $fileName = SetFileNameAction::make($this->attachment->file, $this->attachment->namingMethod, $this->attachment->lang)->generate();

        $this->attachment->output['name'] = $fileName;
        $this->attachment->output['original_name'] = $this->attachment->file->getClientOriginalName();
        $this->attachment->output['id'] = $this->attachment->id;
        $this->attachment->output['format'] = $this->attachment->file->getClientOriginalExtension();
        $this->attachment->output['size'] = $this->attachment->file->getSize();
        $this->attachment->output['type'] = $this->attachment->type->name;
        $this->attachment->output['mime_type'] = $this->attachment->file->getMimeType();
    }

    protected function media(): void
    {
        switch ($this->attachment->type) {
            case LaruploadFileType::VIDEO:
            case LaruploadFileType::AUDIO:
                $meta = $this->ffmpeg()->getMeta();

                $this->attachment->output['width'] = $meta->width;
                $this->attachment->output['height'] = $meta->height;
                $this->attachment->output['duration'] = $meta->duration;

                break;

            case LaruploadFileType::IMAGE:
                $img = $this->img($this->attachment->file);
                $meta = $img->getMeta();

                $this->attachment->output['width'] = $meta['width'];
                $this->attachment->output['height'] = $meta['height'];
                $this->attachment->output['dominant_color'] = $this->attachment->dominantColor
                    ? $img->getDominantColor()
                    : null;

                break;
        }
    }

    protected function setAttributes(Model $model): Model
    {
        if ($this->attachment->mode === LaruploadMode::HEAVY) {
            foreach ($this->attachment->output as $key => $value) {
                $model->{"{$this->attachment->name}_file_$key"} = $value;
            }
        }
        else {
            $model->{"{$this->attachment->name}_file_name"} = $this->attachment->output['name'] ?? null;
            $model->{"{$this->attachment->name}_file_meta"} = json_encode($this->attachment->output);
        }

        return $model;
    }

    protected function uploadOriginalFile(string $id, ?string $disk = null): void
    {
        Storage::disk($disk ?: $this->attachment->disk)
            ->putFileAs(
                path: larupload_relative_path($this->attachment, $id, Larupload::ORIGINAL_FOLDER),
                file: $this->attachment->file,
                name: $this->attachment->output['name']
            );
    }

    protected function setCover($id): void
    {
        $path = larupload_relative_path($this->attachment, $id, Larupload::COVER_FOLDER);
        $data = CoverActionData::make(
            disk: $this->attachment->disk,
            namingMethod: $this->attachment->namingMethod,
            lang: $this->attachment->lang,
            style: $this->attachment->coverStyle,
            type: $this->attachment->type,
            generateCover: $this->attachment->generateCover,
            withDominantColor: $this->attachment->dominantColor,
            dominantColorQuality: $this->attachment->dominantColorQuality,
            imageProcessingLibrary: $this->attachment->imageProcessingLibrary,
            output: $this->attachment->output
        );

        $this->attachment->output = SetCoverAction::make($this->attachment->file ?? null, $this->attachment->cover, $data)->run($path);
    }

    protected function clean(): void
    {
        $this->attachment = resolve(CleanAttachmentAction::class)($this->attachment);
    }
}
