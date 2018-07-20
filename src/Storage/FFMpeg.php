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
        $meta = [
            'width'    => null,
            'height'   => null,
            'duration' => null,
        ];

        try {
            $path = $this->file->getRealPath();
            $cmd = escapeshellcmd("{$this->ffprobe} -i $path -loglevel quiet -show_format -show_streams -print_format json");

            $process = new Process($cmd);
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


        return $meta;
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

        if ($width)
            $scale = $width;
        else if ($height)
            $scale = $height;
        else
            $scale = 850;


        try {
            $path = $this->file->getRealPath();
            if ($mode == 'crop') {
                if ($width and $height)
                    $cmd = escapeshellcmd("{$this->ffmpeg} -ss $fromSecond -i $path -vframes 1 -filter scale=-1:$scale,crop=$width:$height");
                else
                    $cmd = escapeshellcmd("{$this->ffmpeg} -ss $fromSecond -i $path -vframes 1 -filter scale=-1:$scale,crop=$scale:$scale");
            }
            else
                $cmd = escapeshellcmd("{$this->ffmpeg} -ss $fromSecond -i $path -vframes 1 -filter scale=-1:$scale");

            $result = $this->run($cmd, $storage, $saveTo);

            return $result;
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

        if ($width)
            $scale = $width;
        else if ($height)
            $scale = $height;
        else
            $scale = 850;


        try {
            $path = $this->file->getRealPath();

            if ($mode == 'crop') {
                if ($width and $height)
                    $cmd = escapeshellcmd("{$this->ffmpeg} -i $path -vf scale=-1:$scale,crop=$width:$height");
                else
                    $cmd = escapeshellcmd("{$this->ffmpeg} -i $path -vf scale=-1:$scale,crop=$scale:$scale");
            }
            else
                $cmd = escapeshellcmd("{$this->ffmpeg} -i $path -vf scale=-1:$scale");

            $result = $this->run($cmd, $storage, $saveTo);

            return $result;
        }
        catch (Exception $exception) {
            // do nothing
        }

        return false;
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
            $process->run();

            if ($process->isSuccessful()) {
                return true;
            }
        }
        else {
            list($path, $name) = Helper::splitPath($saveTo);

            $tempDir = Helper::tempDir();
            $tempName = time() . '-' . $name;
            $temp = $tempDir . "/" . $tempName;

            $cmd = "$cmd $temp";
            $process = new Process($cmd);
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
}