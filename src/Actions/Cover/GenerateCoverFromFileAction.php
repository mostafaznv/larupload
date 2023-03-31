<?php

namespace Mostafaznv\Larupload\Actions\Cover;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mostafaznv\Larupload\DTOs\CoverActionData;
use Mostafaznv\Larupload\Enums\LaruploadFileType;
use Mostafaznv\Larupload\Storage\FFMpeg\FFMpeg;
use Mostafaznv\Larupload\Storage\Image;

class GenerateCoverFromFileAction
{
    private readonly string $fileName;
    private readonly mixed  $ffmpegCaptureFrame;
    private array           $output;

    public function __construct(private readonly UploadedFile $file, private readonly CoverActionData $data)
    {
        $this->fileName = pathinfo($this->data->output['name'], PATHINFO_FILENAME);
        $this->output = $this->data->output;
    }

    public static function make(UploadedFile $file, CoverActionData $data): static
    {
        return new static($file, $data);
    }


    public function run(string $path): array
    {
        Storage::disk($this->data->disk)->makeDirectory($path);

        $format = $this->fileFormat();
        $name = "$this->fileName.$format";
        $saveTo = "$path/$name";

        switch ($this->data->type) {
            case LaruploadFileType::VIDEO:
                $this->generateCoverFromVideo($saveTo, $name);
                break;

            case LaruploadFileType::IMAGE:
                $this->generateCoverFromImage($saveTo, $name);
                break;
        }

        return $this->output;
    }

    private function generateCoverFromVideo(string $saveTo, string $name): void
    {
        $color = $this->ffmpeg()->capture(
            $this->ffmpegCaptureFrame, $this->data->style, $saveTo, $this->data->withDominantColor
        );

        $this->output['cover'] = $name;
        $this->output['dominant_color'] = $color;
    }

    private function generateCoverFromImage(string $saveTo, string $name): void
    {
        $result = $this->img()->resize($saveTo, $this->data->style);

        if ($result) {
            $this->output['cover'] = $name;
        }
    }

    private function fileFormat(): string
    {
        if ($this->data->type == LaruploadFileType::IMAGE) {
            $format = $this->data->output['format'];

            return $format == 'svg' ? 'png' : $format;
        }

        return 'jpg';
    }

    private function ffmpeg(): FFMpeg
    {
        $this->ffmpegCaptureFrame = config('larupload.ffmpeg.capture-frame');

        return new FFMpeg($this->file, $this->data->disk, $this->data->dominantColorQuality);
    }

    private function img(): Image
    {
        return new Image(
            file: $this->file,
            disk: $this->data->disk,
            library: $this->data->imageProcessingLibrary,
            dominantColorQuality: $this->data->dominantColorQuality
        );
    }
}
