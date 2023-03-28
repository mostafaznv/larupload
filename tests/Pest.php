<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Imagine\Gd\Imagine;
use Imagine\Image\ImageInterface;
use Mostafaznv\Larupload\DTOs\FFMpeg\FFMpegMeta;
use Mostafaznv\Larupload\Enums\LaruploadMode;
use Mostafaznv\Larupload\Storage\Attachment;
use Mostafaznv\Larupload\Storage\FFMpeg\FFMpeg;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadHeavyTestModel;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadLightTestModel;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadSoftDeleteTestModel;
use Mostafaznv\Larupload\Test\TestCase;

uses(TestCase::class)->in(__DIR__);

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toBeExists', function () {
    $baseUrl = config('app.url');
    $url = str_replace($baseUrl, '', $this->value);
    $path = public_path($url);

    return file_exists($path)
        ? true
        : throw new Exception('File not exists');
});

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function save(LaruploadHeavyTestModel|LaruploadLightTestModel|LaruploadSoftDeleteTestModel $model, UploadedFile $file, ?UploadedFile $cover = null): LaruploadHeavyTestModel|LaruploadLightTestModel|LaruploadSoftDeleteTestModel
{
    if ($cover) {
        $model->main_file->attach($file, $cover);
    }
    else {
        $model->main_file = $file;
    }

    $model->save();
    $model->refresh();

    return $model;
}

function urlToImage(string $url): ImageInterface
{
    $baseUrl = url('/');
    $url = str_replace($baseUrl, '', $url);
    $path = public_path($url);

    $image = new Imagine();
    return $image->open($path);
}

function urlToVideo(string $url): FFMpegMeta
{
    $baseUrl = url('/');
    $url = str_replace($baseUrl, '', $url);
    $path = public_path($url);

    $fileName = pathinfo($path, PATHINFO_FILENAME);
    $disk = config('larupload.disk');

    $file = new UploadedFile($path, $fileName, null, null, true);
    $ffmpeg = new FFMpeg($file, $disk);

    return $ffmpeg->getMeta();
}

function urlsToPath(Attachment $attachment): array
{
    $paths = [];
    $baseUrl = url('/');

    foreach ($attachment->urls() as $name => $url) {
        if ($url and $name != 'meta') {
            $paths[] = public_path(str_replace($baseUrl, '', $url));
        }
    }

    return $paths;
}

function macroColumns(LaruploadMode $mode): array
{
    $table = new Blueprint('uploads');
    $table->upload('file', $mode);

    $columns = [];

    foreach ($table->getColumns() as $column) {
        $columns[$column->get('name')] = $column->getAttributes();
    }

    return $columns;
}

function jpg(bool $withFarsiTitle = false): UploadedFile
{
    $path = realpath(__DIR__ . '/Support/Data');

    if ($withFarsiTitle) {
        return new UploadedFile("$path/farsi-name.jpeg", 'تیم بارسلونا.jpeg', 'image/jpeg', null, true);
    }

    return new UploadedFile("$path/image.jpg", 'image.jpg', 'image/jpeg', null, true);
}

function png(): UploadedFile
{
    return new UploadedFile(realpath(__DIR__ . '/Support/Data/image.png'), 'image.png', 'image/png', null, true);
}

function svg(): UploadedFile
{
    return new UploadedFile(realpath(__DIR__ . '/Support/Data/image.svg'), 'image.svg', 'image/svg+xml', null, true);
}

function mp4(): UploadedFile
{
    return new UploadedFile(realpath(__DIR__ . '/Support/Data/video-1.mp4'), 'video-1.mp4', 'video/mp4', null, true);
}

function mp3(): UploadedFile
{
    return new UploadedFile(realpath(__DIR__ . '/Support/Data/audio-1.mp3'), 'audio-1.mp3', 'audio/mpeg', null, true);
}

function pdf(): UploadedFile
{
    return new UploadedFile(realpath(__DIR__ . '/Support/Data/pdf-1.pdf'), 'pdf-1.pdf', 'application/pdf', null, true);
}
