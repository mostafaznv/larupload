<?php

namespace Mostafaznv\Larupload\Concerns\Storage\Attachment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Mostafaznv\Larupload\Actions\SetFileNameAction;
use Mostafaznv\Larupload\Enums\LaruploadFileType;
use Mostafaznv\Larupload\Enums\LaruploadMode;
use Mostafaznv\Larupload\Larupload;

trait BaseAttachment
{
    protected function setBasicDetails(): void
    {
        $fileName = SetFileNameAction::make($this->file, $this->namingMethod, $this->lang)->generate();

        $this->output['name'] = $fileName;
        $this->output['id'] = $this->id;
        $this->output['format'] = $this->file->getClientOriginalExtension();
        $this->output['size'] = $this->file->getSize();
        $this->output['type'] = $this->type->name;
        $this->output['mime_type'] = $this->file->getMimeType();

        if ($this->storeOriginalFileName) {
            $this->output['original_name'] = $this->file->getClientOriginalName();
        }
    }

    protected function setMediaDetails(): void
    {
        switch ($this->type) {
            case LaruploadFileType::VIDEO:
            case LaruploadFileType::AUDIO:
                $meta = $this->ffmpeg()->getMeta();

                $this->output['width'] = $meta->width;
                $this->output['height'] = $meta->height;
                $this->output['duration'] = $meta->duration;

                break;

            case LaruploadFileType::IMAGE:
                $meta = $this->img($this->file)->getMeta();

                $this->output['width'] = $meta['width'];
                $this->output['height'] = $meta['height'];
                $this->output['dominant_color'] = $this->dominantColor
                    ? $this->img($this->file)->getDominantColor($this->file)
                    : null;

                break;
        }
    }

    /**
     * Set attributes before saving event
     */
    protected function setAttributes(Model $model): Model
    {
        if ($this->mode === LaruploadMode::HEAVY) {
            foreach ($this->output as $key => $value) {
                if ($key == 'original_name' and !$this->storeOriginalFileName) {
                    continue;
                }

                $model->{"{$this->name}_file_$key"} = $value;
            }
        }
        else {
            $model->{"{$this->name}_file_name"} = $this->output['name'] ?? null;
            $model->{"{$this->name}_file_meta"} = json_encode($this->output);
        }

        return $model;
    }

    /**
     * Upload original file
     */
    protected function uploadOriginalFile(string $id, ?string $disk = null): void
    {
        Storage::disk($disk ?: $this->disk)
            ->putFileAs(
                path: $this->getBasePath($id, Larupload::ORIGINAL_FOLDER),
                file: $this->file,
                name: $this->output['name']
            );
    }

    /**
     * Clean directory before upload
     */
    protected function clean($id): void
    {
        $path = $this->getBasePath($id);
        Storage::disk($this->disk)->deleteDirectory($path);

        foreach ($this->output as $key => $value) {
            $this->output[$key] = null;
        }
    }
}
