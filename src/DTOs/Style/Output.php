<?php

namespace Mostafaznv\Larupload\DTOs\Style;

use Illuminate\Database\Eloquent\Model;
use Mostafaznv\Larupload\Enums\LaruploadFileType;
use Mostafaznv\Larupload\Enums\LaruploadMode;
use Mostafaznv\Larupload\Traits\Makable;


/**
 * @method static self make(?string $id = null, ?string $name = null, ?string $originalName = null, ?int $size = null, ?LaruploadFileType $type = null, ?string $mimeType = null, ?int $width = null, ?int $height = null, ?int $duration = null, ?string $dominantColor = null, ?string $format = null, ?string $cover = null)
 */
class Output
{
    use Makable;

    public function __construct(
        public ?string            $id = null,
        public ?string            $name = null,
        public ?string            $originalName = null,
        public ?int               $size = null,
        public ?LaruploadFileType $type = null,
        public ?string            $mimeType = null,
        public ?int               $width = null,
        public ?int               $height = null,
        public ?int               $duration = null,
        public ?string            $dominantColor = null,
        public ?string            $format = null,
        public ?string            $cover = null,
    ) {}

    public static function fromModel(Model $model, string $name, LaruploadMode $mode): static
    {
        if ($mode === LaruploadMode::HEAVY) {
            $property = fn(string $key) => $model->{"{$name}_file_$key"} ?? null;
        }
        else {
            $meta = json_decode($model->{"{$name}_file_meta"}, true);

            $property = fn(string $key) => $meta[$key] ?? null;
        }

        $type = $property('type');


        $output = self::make();
        $output->id = $property('id');
        $output->name = $property('name');
        $output->originalName = $property('original_name');
        $output->size = $property('size');
        $output->type = $type ? LaruploadFileType::from($type) : null;
        $output->mimeType = $property('mime_type');
        $output->width = $property('width');
        $output->height = $property('height');
        $output->duration = $property('duration');
        $output->dominantColor = $property('dominant_color');
        $output->format = $property('format');
        $output->cover = $property('cover');


        return $output;
    }


    public function get(string $key): mixed
    {
        return match ($key) {
            'original_name'  => $this->originalName,
            'mime_type'      => $this->mimeType,
            'dominant_color' => $this->dominantColor,
            default          => $this->$key ?? null,
        };
    }

    public function set(string $key, mixed $value): static
    {
        switch ($key) {
            case 'original_name':
                $this->originalName = $value;
                break;

            case 'mime_type':
                $this->mimeType = $value;
                break;

            case 'dominant_color':
                $this->dominantColor = $value;
                break;

            case 'type':
                $this->type = $value ? LaruploadFileType::from($value) : null;
                break;

            default:
                $this->$key = $value;

                break;
        }

        return $this;
    }

    public function save(Model $model, string $name, LaruploadMode $mode): Model
    {
        if ($mode === LaruploadMode::HEAVY) {
            $attr = fn(string $key) => "{$name}_file_$key";

            $model->setAttribute($attr('id'), $this->id);
            $model->setAttribute($attr('name'), $this->name);
            $model->setAttribute($attr('original_name'), $this->originalName);
            $model->setAttribute($attr('size'), $this->size);
            $model->setAttribute($attr('type'), $this->type?->name ?? null);
            $model->setAttribute($attr('mime_type'), $this->mimeType);
            $model->setAttribute($attr('width'), $this->width);
            $model->setAttribute($attr('height'), $this->height);
            $model->setAttribute($attr('duration'), $this->duration);
            $model->setAttribute($attr('dominant_color'), $this->dominantColor);
            $model->setAttribute($attr('format'), $this->format);
            $model->setAttribute($attr('cover'), $this->cover);
        }
        else {
            $model->{"{$name}_file_name"} = $this->name;
            $model->{"{$name}_file_meta"} = json_encode($this->toObject(false));
        }

        return $model;
    }

    public function toObject(bool $camelCase): object
    {
        $data = [
            'id'       => $this->id,
            'name'     => $this->name,
            'size'     => $this->size,
            'type'     => $this->type?->name ?? null,
            'width'    => $this->width,
            'height'   => $this->height,
            'duration' => $this->duration,
            'format'   => $this->format,
            'cover'    => $this->cover,
        ];

        if ($camelCase) {
            return (object)[
                ...$data,
                'originalName'  => $this->originalName,
                'mimeType'      => $this->mimeType,
                'dominantColor' => $this->dominantColor,
            ];
        }

        return (object)[
            ...$data,
            'original_name'  => $this->originalName,
            'mime_type'      => $this->mimeType,
            'dominant_color' => $this->dominantColor,
        ];
    }
}
