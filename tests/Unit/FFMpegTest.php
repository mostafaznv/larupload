<?php

use FFMpeg\Exception\RuntimeException as FFmpegRuntimeException;
use FFMpeg\Format\Video\X264;
use FFMpeg\Media\Audio;
use FFMpeg\Media\Video;
use Illuminate\Http\UploadedFile;
use Imagine\Imagick\Imagine;
use Mostafaznv\Larupload\DTOs\FFMpeg\FFMpegMeta;
use Mostafaznv\Larupload\DTOs\Style\StreamStyle;
use Mostafaznv\Larupload\DTOs\Style\VideoStyle;
use Mostafaznv\Larupload\Storage\FFMpeg\FFMpeg;
use Mostafaznv\Larupload\DTOs\Style\ImageStyle;
use Mostafaznv\Larupload\Enums\LaruploadMediaStyle;
use Illuminate\Support\Facades\Storage;

beforeEach(function() {
    $this->ffmpeg = new FFMpeg(mp4(), 'local', 10);
});

it('will return an instance of ffmpeg', function() {
    $ffmpeg = new FFMpeg(mp4(), 'local', 10);
    expect($ffmpeg->getMedia())->toBeInstanceOf(Video::class);

    $ffmpeg = new FFMpeg(mp3(), 'local', 10);
    expect($ffmpeg->getMedia())->toBeInstanceOf(Audio::class);
});

it('will meta for video files', function() {
    $ffmpeg = new FFMpeg(mp4(), 'local', 10);
    $meta = $ffmpeg->getMeta();

    expect($meta)
        ->toBeInstanceOf(FFMpegMeta::class)
        ->and($meta->width)
        ->toBe(560)
        ->and($meta->height)
        ->toBe(320)
        ->and($meta->duration)
        ->toBe(5);
});

it('will meta for audio files', function() {
    $ffmpeg = new FFMpeg(mp3(), 'local', 10);
    $meta = $ffmpeg->getMeta();

    expect($meta)
        ->toBeInstanceOf(FFMpegMeta::class)
        ->and($meta->width)
        ->toBeNull()
        ->and($meta->height)
        ->toBeNull()
        ->and($meta->duration)
        ->toBe(67);
});

it('can set meta from outside', function() {
    $this->ffmpeg->setMeta(
        FFMpegMeta::make(300, 200, 6)
    );

    $meta = $this->ffmpeg->getMeta();

    expect($meta->width)
        ->toBe(300)
        ->and($meta->height)
        ->toBe(200)
        ->and($meta->duration)
        ->toBe(6);
});

it('can capture screenshots from videos', function(int|null $fromSeconds, ImageStyle $style, int $width, int $height) {
    $fileName = 'cover.jpg';
    $path = get_larupload_save_path('local', $fileName)['local'];

    expect(file_exists($path))->toBeFalse();

    $this->ffmpeg->capture($fromSeconds, $style, $fileName);

    expect(file_exists($path))->toBeTrue();

    $image = new Imagine();
    $image = $image->open($path);
    $size = $image->getSize();

    expect($size->getWidth())
        ->toBe($width)
        ->and($size->getHeight())
        ->toBe($height);

    @unlink($path);

})->with([
    fn() => [
        'fromSeconds' => 0,
        'style'       => ImageStyle::make('cover', 400, 300, LaruploadMediaStyle::FIT),
        'width'       => 400,
        'height'      => 300,
    ],
    fn() => [
        'fromSeconds' => 1,
        'style'       => ImageStyle::make('cover', 400, 300, LaruploadMediaStyle::AUTO),
        'width'       => 400,
        'height'      => 226,
    ],
    fn() => [
        'fromSeconds' => 2,
        'style'       => ImageStyle::make('cover', null, 300, LaruploadMediaStyle::SCALE_WIDTH),
        'width'       => 534,
        'height'      => 300,
    ],
    fn() => [
        'fromSeconds' => 3,
        'style'       => ImageStyle::make('cover', 400, null, LaruploadMediaStyle::SCALE_HEIGHT),
        'width'       => 400,
        'height'      => 226,
    ],
    fn() => [
        'fromSeconds' => 4,
        'style'       => ImageStyle::make('cover', 400, 300, LaruploadMediaStyle::CROP),
        'width'       => 400,
        'height'      => 300,
    ],
    fn() => [
        'fromSeconds' => 5,
        'style'       => ImageStyle::make('cover', 400, 300, LaruploadMediaStyle::CROP),
        'width'       => 400,
        'height'      => 300,
    ],
    fn() => [
        'fromSeconds' => null, // center
        'style'       => ImageStyle::make('cover', 400, 300, LaruploadMediaStyle::CROP),
        'width'       => 400,
        'height'      => 300,
    ]
]);

it('can upload captured screenshots to remote disks', function() {
    $disk = 's3';
    Storage::fake($disk);

    $ffmpeg = new FFMpeg(mp4(), $disk, 10);
    $ffmpeg->capture(
        fromSeconds: 2,
        style: ImageStyle::make('cover', 400, 300, LaruploadMediaStyle::CROP),
        saveTo: 'cover.jpg'
    );

    $files = Storage::disk($disk)->allFiles();

    expect($files)
        ->toBeArray()
        ->toHaveCount(1)
        ->toMatchArray([
            'cover.jpg'
        ]);
});

it('wont capture screenshot if from second is wrong', function() {
    $fileName = 'cover.jpg';
    $path = get_larupload_save_path('local', $fileName)['local'];

    try {
        $this->ffmpeg->capture(
            fromSeconds: 100,
            style: ImageStyle::make('cover', 400, 300, LaruploadMediaStyle::FIT),
            saveTo: $fileName
        );
    }
    catch (RuntimeException $e) {
        expect($e->getMessage())->toBe('Unable to save frame');
    }

    expect(file_exists($path))->toBeFalse();
});

it('wont capture screenshot if save-to path is not exist', function() {
    $fileName = 'not-exist/cover.jpg';
    $path = get_larupload_save_path('local', $fileName)['local'];

    try {
        $this->ffmpeg->capture(
            fromSeconds: 1,
            style: ImageStyle::make('cover', 400, 300, LaruploadMediaStyle::FIT),
            saveTo: $fileName
        );
    }
    catch (RuntimeException $e) {
        expect($e->getMessage())->toBe('Unable to save frame');
    }

    expect(file_exists($path))->toBeFalse();
});

it('can guess dominant color during capturing process', function() {
    $color = $this->ffmpeg->capture(
        fromSeconds: 1,
        style: ImageStyle::make('cover', 400, 300, LaruploadMediaStyle::FIT),
        saveTo: 'cover.jpg',
        withDominantColor: true
    );

    expect($color)->toBeIn(['#7a4e2a', '#794e2a']);
});

it('will throw exception during capture, if media is not a video', function() {
    $ffmpeg = new FFMpeg(mp3(), 'local', 10);

    $ffmpeg->capture(
        fromSeconds: 1,
        style: ImageStyle::make('cover', 400, 300, LaruploadMediaStyle::CROP),
        saveTo: 'cover.jpg'
    );
})->throws(Exception::class);

it('can manipulate videos', function(VideoStyle $style, int $width, int $height) {
    $fileName = 'video.mp4';
    $path = get_larupload_save_path('local', $fileName)['local'];

    $this->ffmpeg->manipulate($style, $fileName);

    expect(file_exists($path))->toBeTrue();

    $file = new UploadedFile($path, $fileName, null, null, true);
    $video = new FFMpeg($file, 'local', 10);
    $meta = $video->getMeta();

    expect($meta->width)
        ->toBe($width)
        ->and($meta->height)
        ->toBe($height)
        ->and($meta->duration)
        ->toBe(5);

    @unlink($path);

})->with([
    fn() => [
        'style'  => VideoStyle::make('fit', 400, 300, LaruploadMediaStyle::FIT),
        'width'  => 400,
        'height' => 300,
    ],
    fn() => [
        'style'  => VideoStyle::make('auto', 400, 300, LaruploadMediaStyle::AUTO),
        'width'  => 400,
        'height' => 226,
    ],
    fn() => [
        'style'  => VideoStyle::make('scale-width', null, 300, LaruploadMediaStyle::SCALE_WIDTH),
        'width'  => 534,
        'height' => 300,
    ],
    fn() => [
        'style'  => VideoStyle::make('scale-height', 400, null, LaruploadMediaStyle::SCALE_HEIGHT),
        'width'  => 400,
        'height' => 226,
    ],
    fn() => [
        'style'  => VideoStyle::make('crop', 400, 300, LaruploadMediaStyle::CROP),
        'width'  => 400,
        'height' => 300,
    ],
    fn() => [
        'style'  => VideoStyle::make('cover', 400, 300, LaruploadMediaStyle::SCALE_HEIGHT, new X264(), true),
        'width'  => 400,
        'height' => 300
    ]
]);

it('can upload manipulated videos to remote disks', function() {
    $disk = 's3';
    Storage::fake($disk);

    $ffmpeg = new FFMpeg(mp4(), $disk, 10);
    $ffmpeg->manipulate(
        style: VideoStyle::make('crop', 400, 300, LaruploadMediaStyle::CROP),
        saveTo: 'video.mp4'
    );

    $files = Storage::disk($disk)->allFiles();

    expect($files)
        ->toBeArray()
        ->toHaveCount(1)
        ->toMatchArray([
            'video.mp4'
        ]);
});

it('can stream videos', function() {
    $fileName = 'stream.m3u8';
    $path = get_larupload_save_path('local', '')['local'];
    $folders = ['lq', 'mq'];

    $styles = [
        StreamStyle::make($folders[0], 400, 300, new X264, LaruploadMediaStyle::CROP),
        StreamStyle::make($folders[1], 500, 400, new X264, LaruploadMediaStyle::SCALE_WIDTH, true),
    ];

    $this->ffmpeg->stream($styles, '/', $fileName);

    $m3u8path = $path . '/' . $fileName;

    expect(file_exists($m3u8path))->toBeTrue();

    foreach ($folders as $folder) {
        $folderPath = $path . '/' . $folder;

        expect(file_exists($folderPath . '/' . "$folder-list.m3u8"))
            ->toBeTrue()
            ->and(file_exists($folderPath . '/' . "$folder-0.ts"))
            ->toBeTrue();

        rmRf($folderPath);
    }
});

it('can upload streams to remote disks', function() {
    $disk = 's3';
    Storage::fake($disk);

    $ffmpeg = new FFMpeg(mp4(), $disk, 10);
    $ffmpeg->stream(
        styles: [
            StreamStyle::make('lq', 300, 200, new X264, LaruploadMediaStyle::SCALE_WIDTH),
        ],
        basePath: '/',
        fileName: 'stream.m3u8'
    );

    $files = Storage::disk($disk)->allFiles();
    $directories = Storage::disk($disk)->allDirectories();

    expect($files)
        ->toBeArray()
        ->toHaveCount(4)
        ->toMatchArray([
            'lq/lq-0.ts',
            'lq/lq-list.m3u8',
            'lq/master.m3u8',
            'stream.m3u8'
        ])
        ->and($directories)
        ->toBeArray()
        ->toHaveCount(1)
        ->toMatchArray([
            'lq'
        ]);
});

it('can clone itself', function() {
    $cloned = $this->ffmpeg->clone();
    expect($cloned)->toBeInstanceOf(FFMpeg::class);

    $cloned = $this->ffmpeg->clone(true);
    expect($cloned)->toBeInstanceOf(FFMpeg::class);
});

it('will throw error on process timeout', function() {
    config()->set('larupload.ffmpeg.timeout', 1);
    config()->set('larupload.ffmpeg.threads', 1);

    $fileName = 'stream.m3u8';

    $styles = [
        StreamStyle::make('lq', 400, 300, new X264, LaruploadMediaStyle::SCALE_HEIGHT),
        StreamStyle::make('mq', 500, 400, new X264, LaruploadMediaStyle::SCALE_HEIGHT),
    ];


    $ffmpeg = new FFMpeg(mp4(), 'local', 10);
    $ffmpeg->stream($styles, '/', $fileName);

})->throws(FFmpegRuntimeException::class);

it('wont fail if logging-channel is false', function() {
    config()->set('larupload.ffmpeg.log-channel', false);

    $ffmpeg = new FFMpeg(mp4(), 'local', 10);

    expect($ffmpeg)->toBeInstanceOf(FFMpeg::class);
});
