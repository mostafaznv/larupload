<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Imagine\Gd\Imagine;
use Imagine\Image\ImageInterface;
use Mostafaznv\Larupload\DTOs\FFMpeg\FFMpegMeta;
use Mostafaznv\Larupload\Enums\LaruploadMode;
use Mostafaznv\Larupload\Storage\Proxy\AttachmentProxy;
use Mostafaznv\Larupload\Storage\FFMpeg\FFMpeg;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadHeavyTestModel;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadLightTestModel;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadSoftDeleteTestModel;
use Mostafaznv\Larupload\Test\TestCase;

uses(TestCase::class)
    ->beforeEach(function() {
        $this->metaKeys = [
            'name', 'id', 'size', 'type', 'mime_type', 'width', 'height',
            'duration', 'dominant_color', 'format', 'cover'
        ];
    })
    ->afterEach(function() {
        $tempBasePath = larupload_temp_dir() . '/test-files';

        if (is_dir($tempBasePath)) {
            array_map('unlink', glob("$tempBasePath/*.*"));
        }

        $path = public_path('uploads');

        if (is_dir($path)) {
            rmRf($path);
        }
    })
    ->in(__DIR__);

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toBeExists', function() {
    $baseUrl = config('app.url');
    $url = str_replace($baseUrl, '', $this->value);
    $path = public_path($url);

    return file_exists($path)
        ? true
        : throw new Exception('File not exists');
});

expect()->extend('toNotExists', function() {
    $baseUrl = config('app.url');
    $url = str_replace($baseUrl, '', $this->value);
    $path = public_path($url);

    return file_exists($path)
        ? throw new Exception('File exists')
        : true;
});

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function save(LaruploadHeavyTestModel|LaruploadLightTestModel|LaruploadSoftDeleteTestModel $model, UploadedFile $file, ?UploadedFile $cover = null): LaruploadHeavyTestModel|LaruploadLightTestModel|LaruploadSoftDeleteTestModel
{
    if ($cover) {
        $model->attachment('main_file')->attach($file, $cover);
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
    $ffmpeg = new FFMpeg($file, $disk, 10);

    return $ffmpeg->getMeta();
}

function urlsToPath(AttachmentProxy $attachment): array
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

function rmRf(string $path): void
{
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($files as $file) {
        if ($file->isDir()) {
            rmdir($file->getRealPath());
        }
        else {
            unlink($file->getRealPath());
        }
    }

    rmdir($path);
}

function copyFile(string $path, string $name, string $mimeType, int $error = 0): UploadedFile
{
    $tempBasePath = larupload_temp_dir() . '/test-files';
    $tempFile = $tempBasePath . '/' . basename($path);

    @mkdir($tempBasePath);
    copy($path, $tempFile);

    return new UploadedFile($tempFile, $name, $mimeType, $error, true);
}

function jpg(bool $withFarsiTitle = false): UploadedFile
{
    $path = realpath(__DIR__ . '/Support/Data');

    if ($withFarsiTitle) {
        return copyFile("$path/farsi-name.jpeg", 'تیم بارسلونا.jpeg', 'image/jpeg');
    }

    return copyFile("$path/image.jpg", 'image.jpg', 'image/jpeg');
}

function png(int $error = 0): UploadedFile
{
    return copyFile(realpath(__DIR__ . '/Support/Data/image.png'), 'image.png', 'image/png', $error);
}

function webp(): UploadedFile
{
    return copyFile(realpath(__DIR__ . '/Support/Data/image.webp'), 'image.webp', 'image/webp');
}

function svg(): UploadedFile
{
    return copyFile(realpath(__DIR__ . '/Support/Data/image.svg'), 'image.svg', 'image/svg+xml');
}

function gif(): UploadedFile
{
    return copyFile(realpath(__DIR__ . '/Support/Data/image.gif'), 'image.gif', 'image/gif');
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

function zip(): UploadedFile
{
    return new UploadedFile(realpath(__DIR__ . '/Support/Data/compress.zip'), 'compress.zip', 'application/zip', null, true);
}

function php(): UploadedFile
{
    return new UploadedFile(realpath(__DIR__ . '/Support/Data/php.php'), 'pdf-1.pdf', 'text/x-php', null, true);
}
