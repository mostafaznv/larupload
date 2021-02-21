<?php

namespace Mostafaznv\Larupload\Storage;

use Exception;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;
use Illuminate\Support\Facades\Storage;
use Mostafaznv\Larupload\Helpers\Helper;
use Symfony\Component\Process\Process;

class FFMpeg
{
    /**
     * Attached file
     *
     * @var object
     */
    protected $file;

    /**
     * Larupload configurations
     *
     * @var array
     */
    protected $config;

    /**
     * Video Metadata
     *
     * @var array
     */
    protected $meta = [];

    /**
     * FFMPEG binary address
     *
     * @var string
     */
    protected $ffmpeg;

    /**
     * FFProbe binary address
     *
     * @var string
     */
    protected $ffprobe;

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
     */
    public function __construct(UploadedFile $file)
    {
        $this->config = config('larupload');
        $this->file = $file;

        if (count($this->config['ffmpeg']) and $this->config['ffmpeg']['ffmpeg.binaries'] and $this->config['ffmpeg']['ffprobe.binaries']) {
            $this->ffmpeg = $this->config['ffmpeg']['ffmpeg.binaries'];
            $this->ffprobe = $this->config['ffmpeg']['ffprobe.binaries'];
        }
        else {
            $this->ffmpeg = 'ffmpeg';
            $this->ffprobe = 'ffprobe';
        }
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
                'width'    => null,
                'height'   => null,
                'duration' => null,
            ];

            $cmd = $this->cmd("{$this->ffprobe} -i $path -loglevel quiet -show_format -show_streams -print_format json");

            $process = new Process($cmd);
            $process->setTimeout($this->config['ffmpeg-timeout']);
            $process->run();
            $output = $process->getOutput();

            if ($process->isSuccessful()) {
                $output = json_decode($output);
                if ($output !== null) {
                    $stream = $output->streams[0];

                    $meta['width'] = isset($stream->width) ? (int)$stream->width : null;
                    $meta['height'] = isset($stream->height) ? (int)$stream->height : null;
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
     * @param string $storage
     * @param string $saveTo
     * @throws Exception
     */
    public function capture($fromSecond, array $style, string $storage, string $saveTo): void
    {
        $width = isset($style['width']) ? $style['width'] : null;
        $height = isset($style['height']) ? $style['height'] : null;
        $scale = $width ? $width : ($height ? $height : 850);
        $mode = isset($style['mode']) ? $style['mode'] : null;
        $saveTo = Storage::disk($storage)->path($saveTo);
        $path = $this->file->getRealPath();

        if (is_null($fromSecond)) {
            $meta = $this->getMeta();
            $fromSecond = floor($meta['duration'] / 2);
            $fromSecond = number_format($fromSecond, 1);
        }

        if ($mode == 'crop') {
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

        $this->run($cmd, $storage, $saveTo);
    }

    /**
     * Manipulate original video file to crop/resize
     *
     * @param $style
     * @param $storage
     * @param $saveTo
     * @throws Exception
     */
    public function manipulate($style, $storage, $saveTo): void
    {
        $width = isset($style['width']) ? $style['width'] : null;
        $height = isset($style['height']) ? $style['height'] : null;
        $mode = isset($style['mode']) ? $style['mode'] : null;
        $scale = $this->calculateScale($mode, $width, $height);
        $saveTo = Storage::disk($storage)->path($saveTo);
        $path = $this->file->getRealPath();

        if ($mode == 'crop') {
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

        $this->run($cmd, $storage, $saveTo);
    }

    /**
     * Stream - Generate HLS video from source file
     *
     * @param array $styles
     * @param string $storage
     * @param string $basePath
     * @param string $fileName
     * @return bool
     * @throws Exception
     */
    public function stream(array $styles, string $storage, string $basePath, string $fileName)
    {
        $playlist = "#EXTM3U\n#EXT-X-VERSION:3\n";
        $converted = [];

        /*
         * Generate multiple video qualities from uploaded video.
         */
        foreach ($styles as $name => $style) {
            $path = $this->file->getRealPath();
            $width = $style['width'];
            $height = $style['height'];
            $audioBitRate = $this->shortNumberToInteger($style['bitrate']['audio']);
            $videoBitRate = $this->shortNumberToInteger($style['bitrate']['video']);
            $styleBasePath = "$basePath/$name-convert";

            Storage::disk('local')->makeDirectory($styleBasePath);
            $saveTo = Storage::disk('local')->path("$styleBasePath/$name.mp4");

            $cmd = escapeshellcmd("{$this->ffmpeg} -y -i $path -s {$width}x{$height} -y -strict experimental -acodec aac -b:a $audioBitRate -ac 2 -ar 48000 -vcodec libx264 -vprofile main -g 48 -b:v $videoBitRate -threads 64");
            $this->run($cmd, 'local', $saveTo);

            $converted[$name] = [
                'path'      => $styleBasePath,
                'file'      => $saveTo,
                'bandwidth' => $videoBitRate,
                'width'     => $width,
                'height'    => $height,
            ];
        }

        /*
         * Convert generated videos to ts
         */
        foreach ($converted as $name => $value) {
            $m3u8 = 'chunk-list.m3u8';
            $streamBasePath = "$basePath/$name";
            Storage::disk($storage)->makeDirectory($streamBasePath);
            $streamBasePath = Storage::disk($storage)->path($streamBasePath);

            $cmd = escapeshellcmd("{$this->ffmpeg} -y -i {$value['file']} -hls_time 9 -hls_segment_filename :stream-path/file-sequence-%d.ts -hls_playlist_type vod :stream-path/$m3u8");
            $this->streamRun($cmd, $storage, $streamBasePath);

            $playlist .= "#EXT-X-STREAM-INF:BANDWIDTH={$value['bandwidth']},RESOLUTION={$value['width']}x{$value['height']}\n";
            $playlist .= "$name/$m3u8\n";

            Storage::disk('local')->deleteDirectory($value['path']);
        }

        if (count($converted)) {
            Storage::disk($storage)->put("$basePath/$fileName", $playlist);
            return true;
        }

        return false;
    }

    /**
     * Calculate scale
     *
     * @param $mode
     * @param $width
     * @param $height
     * @return float|string
     * @throws Exception
     */
    protected function calculateScale(string $mode, $width, $height)
    {
        $meta = $this->getMeta();

        if ($mode == 'crop') {
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
     * @param string $storage
     * @param string $saveTo
     * @throws Exception
     */
    protected function run(string $cmd, string $storage, string $saveTo): void
    {
        $driver = Helper::diskToDriver($storage);

        if ($driver == 'local') {
            $cmd = $this->cmd("$cmd $saveTo");
            $process = new Process($cmd);
            $process->setTimeout($this->config['ffmpeg-timeout']);
            $process->run();

            if ($process->isSuccessful()) {
                return;
            }

            throw new Exception($process->getErrorOutput());
        }
        else {
            list($path, $name) = Helper::splitPath($saveTo);

            $tempDir = Helper::tempDir();
            $tempName = time() . '-' . $name;
            $temp = $tempDir . '/' . $tempName;

            $cmd = $this->cmd("$cmd $temp");
            $process = new Process($cmd);
            $process->setTimeout($this->config['ffmpeg-timeout']);
            $process->run();

            if ($process->isSuccessful()) {
                $file = new File($temp);

                Storage::disk($storage)->putFileAs($path, $file, $name);
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
     * @param string $storage
     * @param string $streamPath
     * @throws Exception
     */
    protected function streamRun(string $cmd, string $storage, string $streamPath): void
    {
        $driver = Helper::diskToDriver($storage);

        if ($driver == 'local') {
            $cmd = $this->cmd(str_replace(':stream-path', $streamPath, $cmd));

            $process = new Process($cmd);
            $process->setTimeout($this->config['ffmpeg-timeout']);
            $process->run();

            if ($process->isSuccessful()) {
                return;
            }

            throw new Exception($process->getErrorOutput());
        }
        else {
            list($path, $name) = Helper::splitPath($streamPath);

            $temp = $name . '-' . time();

            Storage::disk('local')->makeDirectory($temp);

            $cmd = $this->cmd(str_replace(':stream-path', $temp, $cmd));

            $process = new Process($cmd);
            $process->setTimeout($this->config['ffmpeg-timeout']);
            $process->run();

            if ($process->isSuccessful()) {
                $files = Storage::disk('local')->files($temp);

                foreach ($files as $file) {
                    $fileObject = new File($file);

                    Storage::disk($storage)->putFileAs($streamPath, $fileObject, $fileObject->getFilename());

                    unset($fileObject);
                }

                Storage::disk('local')->deleteDirectory($temp);

                return;
            }

            throw new Exception($process->getErrorOutput());
        }
    }

    /**
     * Convert short number formats to integer
     * Example: 1M -> 1000000
     *
     * @param $number
     * @return int
     */
    protected function shortNumberToInteger($number): int
    {
        $number = strtoupper($number);

        $units = [
            'M' => '1000000',
            'K' => '1000',
        ];

        $unit = substr($number, -1);

        if (!array_key_exists($unit, $units)) {
            return (int)$number;
        }

        $number = (float)$number * $units[$unit];

        return (int)$number;
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
