<?php

namespace Mostafaznv\Larupload\Concerns\Storage;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Mostafaznv\Larupload\LaruploadEnum;

trait BaseAttachment
{
    protected function setBasicDetails(): void
    {
        $this->output['name'] = $this->setFileName();
        $this->output['format'] = $this->file->getClientOriginalExtension();
        $this->output['size'] = $this->file->getSize();
        $this->output['type'] = $this->type;
        $this->output['mime_type'] = $this->file->getMimeType();
    }

    protected function setMediaDetails(): void
    {
        switch ($this->type) {
            case LaruploadEnum::VIDEO:
            case LaruploadEnum::AUDIO:
                $meta = $this->ffmpeg()->getMeta();

                $this->output['width'] = $meta['width'];
                $this->output['height'] = $meta['height'];
                $this->output['duration'] = $meta['duration'];

                break;

            case LaruploadEnum::IMAGE:
                $meta = $this->image($this->file)->getMeta();

                $this->output['width'] = $meta['width'];
                $this->output['height'] = $meta['height'];
                $this->output['dominant_color'] = $this->dominantColor ? $this->image($this->file)->getDominantColor($this->file) : null;

                break;
        }
    }

    /**
     * Set attributes before saving event
     */
    protected function setAttributes(Model $model): Model
    {
        if ($this->mode == 'heavy') {
            foreach ($this->output as $key => $value) {
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
    protected function uploadOriginalFile(int $id): void
    {
        $path = $this->getBasePath($id, LaruploadEnum::ORIGINAL_FOLDER);

        Storage::disk($this->disk)->putFileAs($path, $this->file, $this->output['name']);
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
