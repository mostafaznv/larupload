<?php

namespace Mostafaznv\Larupload\Storage\FFMpeg;


use FFMpeg\Format\Video\X264;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Mostafaznv\Larupload\DTOs\FFMpeg\FFMpegStreamRepresentation;
use Mostafaznv\Larupload\DTOs\Style\StreamStyle;

class HLS
{
    private const MASTER_FILE_NAME = 'master.m3u8';
    private const PLAYLIST_START   = '#EXTM3U';
    private const PLAYLIST_END     = '#EXT-X-ENDLIST';

    private bool  $driverIsLocal;
    private array $playlist;


    public function __construct(private readonly FFMpeg $ffmpeg, private readonly string $disk)
    {
        $this->driverIsLocal = disk_driver_is_local($this->disk);
        $this->playlist = [
            self::PLAYLIST_START
        ];
    }


    public function export(array $styles, string $basePath, string $fileName): bool
    {
        $saveTo = get_larupload_save_path($this->disk, $basePath);

        foreach ($styles as $style) {
            $representation = $this->processRepresentation($style, $saveTo['local']);
            $this->playlist[] = $this->getStreamInfo($representation);
        }

        $res = $this->generatePlaylist($saveTo['local'], $fileName);

        if ($res) {
            larupload_finalize_save($this->disk, $saveTo, true);

            return true;
        }

        // @codeCoverageIgnoreStart
        return false;
        // @codeCoverageIgnoreEnd
    }

    private function processRepresentation(StreamStyle $style, string $path): FFMpegStreamRepresentation
    {
        $ffmpeg = $this->ffmpeg->clone(true);

        $m3u8ListName = "$style->name-list.m3u8";
        $streamBasePath = $this->makeRepresentationDirectory($path, $style->name);
        $saveTo = "$streamBasePath/$m3u8ListName";


        $format = $this->format($style, $streamBasePath);

        $style->mode->ffmpegResizeFilter()
            ? $ffmpeg->resize($style)
            : $ffmpeg->crop($style);

        $ffmpeg->getMedia()->save($format, $saveTo);

        return FFMpegStreamRepresentation::make($style->name, $streamBasePath, $m3u8ListName);
    }

    private function format(StreamStyle $style, string $path): X264
    {
        $format = $style->format;
        $format->setAdditionalParameters([
            '-sc_threshold', '0',
            '-g', '60',
            '-hls_playlist_type', 'vod',
            '-hls_time', '10',
            '-hls_list_size', '0',
            '-hls_segment_filename', "$path/$style->name-%d.ts",
            '-master_pl_name', self::MASTER_FILE_NAME,
        ]);

        return $format;
    }

    private function getStreamInfo(FFMpegStreamRepresentation $representation): string
    {
        $fileName = $representation->path . '/' . self::MASTER_FILE_NAME;
        $file = file_get_contents($fileName);

        $lines = preg_split('/\n|\r\n?/', $file);
        $lines = Collection::make($lines)->filter();

        $list = "$representation->name/$representation->listName";
        $info = $lines->get(
            $lines->search($representation->listName) - 1
        );

        $info = $info . ',NAME="' . $representation->name . '"';

        return implode(PHP_EOL, [$info, $list]);
    }

    private function generatePlaylist(string $path, string $filename): bool
    {
        $this->playlist[] = self::PLAYLIST_END;
        $playlist = implode(PHP_EOL, $this->playlist);

        return File::put("$path/$filename", $playlist);
    }

    private function makeRepresentationDirectory(string $path, string $folder): string
    {
        $directory = "$path/$folder";

        if ($this->driverIsLocal) {
            File::makeDirectory($directory);
        }
        else {
            @mkdir(
                directory: $directory,
                recursive: true
            );
        }

        return $directory;
    }
}
