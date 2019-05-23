<?php

namespace Mostafaznv\Larupload\Storage;

use Exception;
use Symfony\Component\HttpFoundation\File\File;
use Illuminate\Support\Facades\Storage;
use Mostafaznv\Larupload\Helpers\Helper;
use Symfony\Component\Process\Process;

class FFMpeg
{
    /**
     * Attached file.
     *
     * @var object
     */
    protected $file;

    /**
     * Larupload configurations.
     *
     * @var array
     */
    protected $config;

    /**
     * Video Metadata.
     *
     * @var array
     */
    protected $meta = [];

    /**
     * FFMPEG binary address.
     *
     * @var string
     */
    protected $ffmpeg;

    /**
     * FFProbe binary address.
     *
     * @var string
     */
    protected $ffprobe;

    /**
     * Default scale size.
     * we use this value if width and height both were undefined.
     *
     * @var integer
     */
    const DEFAULT_SCALE = 850;

    /**
     * FFMpeg constructor.
     *
     * @param $file
     */
    public function __construct($file)
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
     * Get Metadata from media file using ffprobe.
     *
     * @return array
     */
    public function getMeta()
    {
        if (empty($this->meta)) {
            $meta = [
                'width'    => null,
                'height'   => null,
                'duration' => null,
            ];

            try {
                $path = $this->file->getRealPath();
                $cmd = escapeshellcmd("{$this->ffprobe} -i $path -loglevel quiet -show_format -show_streams -print_format json");

                $process = new Process($cmd);
                $process->setTimeout($this->config['ffmpeg-timeout']);
                $process->run();
                $output = $process->getOutput();

                if ($process->isSuccessful()) {
                    $output = json_decode($output);
                    if ($output !== null) {
                        $stream = $output->streams[0];

                        $meta['width'] = (isset($stream->width)) ? (int)$stream->width : null;
                        $meta['height'] = (isset($stream->height)) ? (int)$stream->height : null;
                        $meta['duration'] = (int)$stream->duration;
                    }
                }
            }
            catch (Exception $exception) {
                // do nothing
            }

            $this->meta = $meta;
        }

        return $this->meta;
    }

    /**
     * Capture screen shot from video file.
     *
     * @param $fromSecond
     * @param $style
     * @param $storage
     * @param $saveTo
     * @return bool
     */
    public function capture($fromSecond, $style, $storage, $saveTo)
    {
        $width = isset($style['width']) ? $style['width'] : null;
        $height = isset($style['height']) ? $style['height'] : null;
        $mode = isset($style['mode']) ? $style['mode'] : null;

        if ($width) {
            $scale = $width;
        }
        else if ($height) {
            $scale = $height;
        }
        else {
            $scale = 850;
        }


        try {
            $path = $this->file->getRealPath();
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


            return $this->run($cmd, $storage, $saveTo);
        }
        catch (Exception $exception) {
            // do nothing
        }

        return false;
    }

    /**
     * Manipulate original video file to crop/resize
     *
     * @param $style
     * @param $storage
     * @param $saveTo
     * @return bool
     */
    public function manipulate($style, $storage, $saveTo)
    {
        $width = isset($style['width']) ? $style['width'] : null;
        $height = isset($style['height']) ? $style['height'] : null;
        $mode = isset($style['mode']) ? $style['mode'] : null;
        $scale = $this->calculateScale($mode, $width, $height);
        $saveTo = Storage::disk($storage)->path($saveTo);

        try {
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

            return $this->run($cmd, $storage, $saveTo);
        }
        catch (Exception $exception) {
            //
        }

        return false;
    }

    /**
     * Stream - Generate HLS video from source file.
     *
     * @param array $styles
     * @param $storage
     * @param $basePath
     * @param $fileName
     * @return bool
     */
    public function stream(array $styles, $storage, $basePath, $fileName)
    {
        $playlist = "#EXTM3U\n#EXT-X-VERSION:3\n";
        $converted = [];

        /*
         * Generate multiple video qualities from uploaded video.
         */
        foreach ($styles as $name => $style) {
            try {
                $path = $this->file->getRealPath();
                $width = $style['width'];
                $height = $style['height'];
                $audioBitRate = $style['bitrate']['audio'];
                $videoBitRate = $style['bitrate']['video'];
                $styleBasePath = "$basePath/$name-convert";

                Storage::disk('local')->makeDirectory($styleBasePath);
                $saveTo = Storage::disk('local')->path("$styleBasePath/$name.mp4");

                $cmd = escapeshellcmd("{$this->ffmpeg} -y -i $path -s {$width}x{$height} -y -strict experimental -acodec aac -b:a $audioBitRate -ac 2 -ar 48000 -vcodec libx264 -vprofile main -g 48 -b:v $videoBitRate -threads 64");
                $convertResult = $this->run($cmd, 'local', $saveTo);


                if ($convertResult) {
                    $converted[$name] = [
                        'path'      => $styleBasePath,
                        'file'      => $saveTo,
                        'bandwidth' => $videoBitRate,
                        'width'     => $width,
                        'height'    => $height,
                    ];
                }
                else {
                    Storage::disk('local')->deleteDirectory($styleBasePath);
                }
            }
            catch (Exception $e) {
                // do nothing
            }
        }

        /*
         * Convert generated videos to ts
         */
        foreach ($converted as $name => $value) {
            try {
                $m3u8 = 'chunk-list.m3u8';
                $streamBasePath = "$basePath/$name";
                Storage::disk($storage)->makeDirectory($streamBasePath);
                $streamBasePath = Storage::disk($storage)->path($streamBasePath);

                $cmd = escapeshellcmd("{$this->ffmpeg} -y -i {$value['file']} -hls_time 9 -hls_segment_filename :stream-path/file-sequence-%d.ts -hls_playlist_type vod :stream-path/$m3u8");
                $streamResult = $this->streamRun($cmd, $storage, $streamBasePath);

                if ($streamResult) {
                    $playlist .= "#EXT-X-STREAM-INF:BANDWIDTH={$value['bandwidth']},RESOLUTION={$value['width']}x{$value['height']}\n";
                    $playlist .= "$name/$m3u8\n";
                }

                Storage::disk('local')->deleteDirectory($value['path']);
            }
            catch (Exception $e) {
                Storage::disk('local')->deleteDirectory($value['path']);
            }
        }

        if (count($converted)) {
            Storage::disk($storage)->put("$basePath/$fileName", $playlist);
            return true;
        }

        return false;
    }

    /**
     * Calculate scale.
     *
     * @param $mode
     * @param $width
     * @param $height
     * @return float|string
     */
    protected function calculateScale($mode, $width, $height)
    {
        $meta = $this->getMeta();

        if ($mode == 'crop') {
            if ($width >= $meta['width'] or $height >= $meta['height']) {
                if ($meta['width'] >= $meta['height']) {
                    $scale = ceil(($meta['width'] * $height) / $meta['height']);
                    //dd($scale, $width, $height, $meta);
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
            if ($width) {
                $scale = "$width:-2";
            }
            else if ($height) {
                $scale = "-2:$height";
            }
            else {
                $scale = self::DEFAULT_SCALE . ':-2';
            }
        }

        return $scale;
    }

    /**
     * Run ffmpeg command.
     * Handle local/non-local drivers
     *
     * @param $cmd
     * @param $storage
     * @param $saveTo
     * @return bool
     */
    protected function run($cmd, $storage, $saveTo)
    {
        $driver = Helper::diskToDriver($storage);

        if ($driver == 'local') {
            $cmd = "$cmd $saveTo";
            $process = new Process($cmd);
            $process->setTimeout($this->config['ffmpeg-timeout']);
            $process->run();

            if ($process->isSuccessful()) {
                return true;
            }
        }
        else {
            list($path, $name) = Helper::splitPath($saveTo);

            $tempDir = Helper::tempDir();
            $tempName = time() . '-' . $name;
            $temp = $tempDir . '/' . $tempName;

            $cmd = "$cmd $temp";
            $process = new Process($cmd);
            $process->setTimeout($this->config['ffmpeg-timeout']);
            $process->run();

            if ($process->isSuccessful()) {
                $file = new File($temp);

                Storage::disk($storage)->putFileAs($path, $file, $name);
                @unlink($temp);

                return true;
            }
        }

        return false;
    }

    /**
     * Run ffmpeg command for stream videos.
     * Handle local/non-local drivers
     *
     * @param $cmd
     * @param $storage
     * @param $streamPath
     * @return bool
     */
    protected function streamRun($cmd, $storage, $streamPath)
    {
        $driver = Helper::diskToDriver($storage);

        if ($driver == 'local') {
            $cmd = str_replace(':stream-path', $streamPath, $cmd);

            $process = new Process($cmd);
            $process->setTimeout($this->config['ffmpeg-timeout']);
            $process->run();


            if ($process->isSuccessful()) {
                return true;
            }
        }
        else {
            list($path, $name) = Helper::splitPath($streamPath);


            $temp = $name . '-' . time();

            Storage::disk('local')->makeDirectory($temp);

            $cmd = str_replace(':stream-path', $temp, $cmd);

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
                return true;
            }
        }

        return false;
    }
}