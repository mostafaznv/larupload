<?php

namespace Mostafaznv\Larupload\Storage;

use Exception;
use Illuminate\Http\UploadedFile;
use Mostafaznv\Larupload\Helpers\LaraTools;
use Mostafaznv\Larupload\LaruploadEnum;
use Symfony\Component\HttpFoundation\File\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class FFMpeg
{
    use LaraTools;

    /**
     * Attached file
     *
     * @var UploadedFile
     */
    protected UploadedFile $file;

    /**
     * Storage Disk
     *
     * @var string
     */
    protected string $disk;

    /**
     * Storage local disk
     *
     * @var string
     */
    protected string $localDisk;

    /**
     * Specify if driver is local or not
     *
     * @var bool
     */
    protected bool $driverIsLocal;

    /**
     * Video Metadata
     *
     * @var array
     */
    protected array $meta = [];

    /**
     * FFMPEG binary address
     *
     * @var string
     */
    protected string $ffmpeg;

    /**
     * FFProbe binary address
     *
     * @var string
     */
    protected string $ffprobe;

    /**
     * Timeout
     *
     * @var int
     */
    protected int $timeout;

    /**
     * Default scale size
     * we use this value if width and height both were undefined
     *
     * @var integer
     */
    const DEFAULT_SCALE = 850;

    /**
     * FFMpeg constructor
     *
     * @param UploadedFile $file
     * @param string $disk
     * @param string $localDisk
     */
    public function __construct(UploadedFile $file, string $disk, string $localDisk)
    {
        $this->file = $file;
        $this->disk = $disk;
        $this->localDisk = $localDisk;
        $this->driverIsLocal = $this->disk == $this->localDisk;

        $config = config('larupload.ffmpeg');

        $this->ffmpeg = $config['ffmpeg-binaries'];
        $this->ffprobe = $config['ffprobe-binaries'];
        $this->timeout = $config['timeout'];
    }

    /**
     * Get Metadata from media file using ffprobe
     *
     * @return array
     * @throws Exception
     */
    public function getMeta(): array
    {
        if (empty($this->meta)) {
            $path = $this->file->getRealPath();
            $meta = [
                'width'    => 0,
                'height'   => 0,
                'duration' => 0,
            ];

            $cmd = $this->cmd("{$this->ffprobe} -i $path -loglevel quiet -show_format -show_streams -print_format json");

            $process = new Process($cmd);
            $process->setTimeout($this->timeout);
            $process->run();
            $output = $process->getOutput();

            if ($process->isSuccessful()) {
                $output = json_decode($output);

                if ($output !== null) {
                    $stream = $output->streams[0];

                    $meta['width'] = (int)$stream->width;
                    $meta['height'] = (int)$stream->height;
                    $meta['duration'] = (int)$stream->duration;
                }
                else {
                    $process->addErrorOutput('ffprobe output is null');
                    throw new Exception($process->getErrorOutput());
                }
            }
            else {
                throw new Exception($process->getErrorOutput());
            }

            $this->meta = $meta;
        }

        return $this->meta;
    }

    /**
     * Capture screen shot from video file
     *
     * @param $fromSecond
     * @param array $style
     * @param string $saveTo
     * @throws Exception
     */
    public function capture($fromSecond, array $style, string $saveTo): void
    {
        $width = isset($style['width']) ? $style['width'] : null;
        $height = isset($style['height']) ? $style['height'] : null;
        $scale = $width ? $width : ($height ? $height : self::DEFAULT_SCALE);
        $mode = isset($style['mode']) ? $style['mode'] : null;
        $path = $this->file->getRealPath();
        $saveTo = Storage::disk($this->disk)->path($saveTo);

        if (is_null($fromSecond)) {
            $meta = $this->getMeta();
            $fromSecond = floor($meta['duration'] / 2);
            $fromSecond = number_format($fromSecond, 1);
        }

        if ($mode == LaruploadEnum::CROP_STYLE_MODE) {
            if ($width and $height) {
                $cmd = escapeshellcmd("{$this->ffmpeg} -ss $fromSecond -i $path -vframes 1 -filter scale=-1:$scale,crop=$width:$height");
            }
            else {
                $cmd = escapeshellcmd("{$this->ffmpeg} -ss $fromSecond -i $path -vframes 1 -filter scale=-1:$scale,crop=$scale:$scale");
            }
        }
        else {
            $cmd = escapeshellcmd("{$this->ffmpeg} -ss $fromSecond -i $path -vframes 1 -filter scale=-1:$scale");
        }

        $this->run($cmd, $saveTo);
    }

    /**
     * Manipulate original video file to crop/resize
     *
     * @param array $style
     * @param string $saveTo
     * @throws Exception
     */
    public function manipulate(array $style, string $saveTo): void
    {
        $width = isset($style['width']) ? $style['width'] : null;
        $height = isset($style['height']) ? $style['height'] : null;
        $mode = isset($style['mode']) ? $style['mode'] : null;
        $scale = $this->calculateScale($mode, $width, $height);
        $path = $this->file->getRealPath();
        $saveTo = Storage::disk($this->disk)->path($saveTo);

        if ($mode == LaruploadEnum::CROP_STYLE_MODE) {
            if ($scale) {
                $cmd = escapeshellcmd("{$this->ffmpeg} -i $path -vf scale=$scale,crop=$width:$height,setsar=1");
            }
            else {
                $cmd = escapeshellcmd("{$this->ffmpeg} -i $path -vf crop=$width:$height,setsar=1");
            }
        }
        else {
            $cmd = escapeshellcmd("{$this->ffmpeg} -i $path -vf scale=$scale,setsar=1");
        }

        $this->run($cmd, $saveTo);
    }

    /**
     * Stream - Generate HLS video from source file
     *
     * @param array $styles
     * @param string $basePath
     * @param string $fileName
     * @return bool
     * @throws Exception
     */
    public function stream(array $styles, string $basePath, string $fileName): bool
    {
        $playlist = "#EXTM3U\n#EXT-X-VERSION:3\n";
        $converted = [];

        // generate multiple video qualities from uploaded video.
        foreach ($styles as $name => $style) {
            $width = $style['width'];
            $height = $style['height'];
            $path = $this->file->getRealPath();
            $audioBitRate = $style['bitrate']['audio'];
            $videoBitRate = $style['bitrate']['video'];
            $styleBasePath = "$basePath/$name-convert";

            Storage::disk($this->localDisk)->makeDirectory($styleBasePath);
            $saveTo = Storage::disk($this->localDisk)->path("$styleBasePath/$name.mp4");

            $cmd = escapeshellcmd("{$this->ffmpeg} -y -i $path -s {$width}x{$height} -y -strict experimental -acodec aac -b:a $audioBitRate -ac 2 -ar 48000 -vcodec libx264 -vprofile main -g 48 -b:v $videoBitRate -threads 64");
            $this->run($cmd, $saveTo, $this->localDisk);

            $converted[$name] = [
                'path'      => $styleBasePath,
                'file'      => $saveTo,
                'bandwidth' => $videoBitRate,
                'width'     => $width,
                'height'    => $height,
            ];
        }

        // convert generated videos to ts
        foreach ($converted as $name => $value) {
            $m3u8 = 'chunk-list.m3u8';
            $streamBasePath = "$basePath/$name";
            Storage::disk($this->disk)->makeDirectory($streamBasePath);
            $streamBasePath = Storage::disk($this->disk)->path($streamBasePath);

            $cmd = escapeshellcmd("{$this->ffmpeg} -y -i {$value['file']} -hls_time 9 -hls_segment_filename :stream-path/file-sequence-%d.ts -hls_playlist_type vod :stream-path/$m3u8");
            $this->streamRun($cmd, $streamBasePath);

            $playlist .= "#EXT-X-STREAM-INF:BANDWIDTH={$value['bandwidth']},RESOLUTION={$value['width']}x{$value['height']}\n";
            $playlist .= "$name/$m3u8\n";

            Storage::disk($this->localDisk)->deleteDirectory($value['path']);
        }

        if (count($converted)) {
            Storage::disk($this->disk)->put("$basePath/$fileName", $playlist);
            return true;
        }

        return false;
    }

    /**
     * Calculate scale
     *
     * @param string $mode
     * @param int $width
     * @param int $height
     * @return string
     * @throws Exception
     */
    protected function calculateScale(string $mode, int $width, int $height): string
    {
        $meta = $this->getMeta();

        if ($mode == LaruploadEnum::CROP_STYLE_MODE) {
            if ($width >= $meta['width'] or $height >= $meta['height']) {
                if ($meta['width'] >= $meta['height']) {
                    $scale = ceil(($meta['width'] * $height) / $meta['height']);

                    if ($scale < $width) {
                        $scale = $width;
                    }

                    $scale = "$scale:-2";
                }
                else {
                    $scale = ceil(($meta['height'] * $width) / $meta['width']);

                    if ($scale < $height) {
                        $scale = $height;
                    }

                    $scale = "-2:$scale";
                }
            }
            else {
                $scale = '';
            }
        }
        else {
            $scale = $width ? "$width:-2" : ($height ? "-2:$height" : (self::DEFAULT_SCALE . ':-2'));
        }

        return $scale;
    }

    /**
     * Run ffmpeg command.
     * Handle local/non-local drivers
     *
     * @param string $cmd
     * @param string $saveTo
     * @param string|null $disk
     * @throws Exception
     */
    protected function run(string $cmd, string $saveTo, string $disk = null): void
    {
        $disk = $disk ?? $this->disk;

        if ($this->driverIsLocal) {
            $cmd = $this->cmd("$cmd $saveTo");
            $process = new Process($cmd);
            $process->setTimeout($this->timeout);
            $process->run();

            if ($process->isSuccessful()) {
                return;
            }

            throw new Exception($process->getErrorOutput());
        }
        else {
            list($path, $name) = $this->splitPath($saveTo);

            $tempDir = $this->tempDir();
            $tempName = time() . '-' . $name;
            $temp = "{$tempDir}/{$tempName}";

            $cmd = $this->cmd("$cmd $temp");
            $process = new Process($cmd);
            $process->setTimeout($this->timeout);
            $process->run();

            if ($process->isSuccessful()) {
                $file = new File($temp);

                Storage::disk($disk)->putFileAs($path, $file, $name);
                @unlink($temp);

                return;
            }

            throw new Exception($process->getErrorOutput());
        }
    }

    /**
     * Run ffmpeg command for stream videos
     * Handle local/non-local drivers
     *
     * @param string $cmd
     * @param string $streamPath
     * @throws Exception
     */
    protected function streamRun(string $cmd, string $streamPath): void
    {
        if ($this->driverIsLocal) {
            $cmd = $this->cmd(str_replace(':stream-path', $streamPath, $cmd));

            $process = new Process($cmd);
            $process->setTimeout($this->timeout);
            $process->run();

            if ($process->isSuccessful()) {
                return;
            }

            throw new Exception($process->getErrorOutput());
        }
        else {
            list($path, $name) = $this->splitPath($streamPath);

            $temp = $name . '-' . time();

            Storage::disk($this->localDisk)->makeDirectory($temp);

            $cmd = $this->cmd(str_replace(':stream-path', $temp, $cmd));

            $process = new Process($cmd);
            $process->setTimeout($this->timeout);
            $process->run();

            if ($process->isSuccessful()) {
                $files = Storage::disk($this->localDisk)->files($temp);

                foreach ($files as $file) {
                    $fileObject = new File($file);

                    Storage::disk($this->disk)->putFileAs($streamPath, $fileObject, $fileObject->getFilename());

                    unset($fileObject);
                }

                Storage::disk($this->localDisk)->deleteDirectory($temp);

                return;
            }

            throw new Exception($process->getErrorOutput());
        }
    }

    /**
     * Make Normal CMD
     *
     * @param string $cmd
     * @return array
     */
    protected function cmd(string $cmd): array
    {
        $cmd = str_replace('\\', '/', $cmd);

        return explode(' ', escapeshellcmd($cmd));
    }
}
