<?php

namespace Mostafaznv\Larupload\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mostafaznv\Larupload\DTOs\Style\ImageStyle;
use Mostafaznv\Larupload\Enums\LaruploadFileType;
use Mostafaznv\Larupload\Enums\LaruploadImageLibrary;
use Mostafaznv\Larupload\Storage\FFMpeg\FFMpeg;
use Mostafaznv\Larupload\Storage\Image;

class GenerateCoverFromFileAction
{
    private readonly string $fileName;

    private readonly ?LaruploadFileType $type;

    /**
     * Specify the time in seconds to capture a frame from the video.
     */
    private readonly mixed $ffmpegCaptureFrame;


    public function __construct(
        private readonly UploadedFile          $file,
        private readonly string                $disk,
        private readonly ImageStyle            $style,
        private readonly bool                  $withDominantColor,
        private readonly LaruploadImageLibrary $imageProcessingLibrary,
        private array                          $output
    ) {
        $this->type = GuessLaruploadFileTypeAction::make($file)();

        $this->fileName = pathinfo($this->output['name'], PATHINFO_FILENAME);
    }

    public static function make(UploadedFile $file, string $disk, ImageStyle $style, bool $withDominantColor, LaruploadImageLibrary $imageProcessingLibrary, array $output): self
    {
        return new self($file, $disk, $style, $withDominantColor, $imageProcessingLibrary, $output);
    }


    public function __invoke(string $path): array
    {
        Storage::disk($this->disk)->makeDirectory($path);

        $format = $this->fileFormat();
        $name = "$this->fileName.$format";
        $saveTo = "$path/$name";

        switch ($this->type) {
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
        $color = $this->ffmpeg()->capture($this->ffmpegCaptureFrame, $this->style, $saveTo, $this->withDominantColor);

        $this->output['cover'] = $name;
        $this->output['dominant_color'] = $color;
    }

    private function generateCoverFromImage(string $saveTo, string $name): void
    {
        $result = $this->img()->resize($saveTo, $this->style);

        if ($result) {
            $this->output['cover'] = $name;
        }
    }

    private function fileFormat(): string
    {
        if ($this->type == LaruploadFileType::IMAGE) {
            return $this->output['format'] == 'svg' ? 'png' : $this->output['format'];
        }

        return 'jpg';
    }

    private function ffmpeg(): FFMpeg
    {
        $this->ffmpegCaptureFrame = config('larupload.ffmpeg.capture-frame');

        return new FFMpeg($this->file, $this->disk);
    }

    private function img(): Image
    {
        return new Image(
            file: $this->file,
            disk: $this->disk,
            library: $this->imageProcessingLibrary
        );
    }
}
