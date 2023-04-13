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

it('can capture screenshots from videos', function(int|null $fromSeconds, ImageStyle $style, int $width, int $height, array $hash) {
    $fileName = 'cover.jpg';
    $path = get_larupload_save_path('local', $fileName)['local'];

    $this->ffmpeg->capture($fromSeconds, $style, $fileName);

    expect(file_exists($path))->toBeTrue();

    $file = new UploadedFile($path, $fileName, null, null, true);
    $image = new Imagine();
    $image = $image->open($path);

    $hashFile = hash_file('md5', $file->getRealPath());
    $size = $image->getSize();

    expect($hashFile)
        ->toBeIn($hash)
        ->and($size->getWidth())
        ->toBe($width)
        ->and($size->getHeight())
        ->toBe($height);

    @unlink($path);

})->with([
    [
        'fromSeconds' => 0,
        'style'       => ImageStyle::make('cover', 400, 300, LaruploadMediaStyle::FIT),
        'width'       => 400,
        'height'      => 300,
        'hash'        => ['ff1190d19a78893233945f6c1ff405ff', 'b0900d5ec361495f121fe122f6867512']
    ],
    [
        'fromSeconds' => 1,
        'style'       => ImageStyle::make('cover', 400, 300, LaruploadMediaStyle::AUTO),
        'width'       => 400,
        'height'      => 226,
        'hash'        => ['7ebea4afbe53f5d52c61973fa94d218a', '2c58b919940e3e9ef551ff10bff3273e']
    ],
    [
        'fromSeconds' => 2,
        'style'       => ImageStyle::make('cover', null, 300, LaruploadMediaStyle::SCALE_WIDTH),
        'width'       => 534,
        'height'      => 300,
        'hash'        => ['a70cd56c065ec6c02fc60dbffcc0326a', '66444a2e3642994f9c67701ca0ec65d2']
    ],
    [
        'fromSeconds' => 3,
        'style'       => ImageStyle::make('cover', 400, null, LaruploadMediaStyle::SCALE_HEIGHT),
        'width'       => 400,
        'height'      => 226,
        'hash'        => ['41f01b4fad7e8212b7563421c3ef7db6', '294363c52d24c6ecf09550d21bf05528']
    ],
    [
        'fromSeconds' => 4,
        'style'       => ImageStyle::make('cover', 400, 300, LaruploadMediaStyle::CROP),
        'width'       => 400,
        'height'      => 300,
        'hash'        => ['a298452b17b6f725655dec733e2caa0c', 'd25d8dae46a853bb291b8c223a1a5165']
    ],
    [
        'fromSeconds' => 5,
        'style'       => ImageStyle::make('cover', 400, 300, LaruploadMediaStyle::CROP),
        'width'       => 400,
        'height'      => 300,
        'hash'        => ['136d39b3469cc80223d0214e6382d155', '57e84a29f42f080d6bc1c97369d1ea0a']
    ],
    [
        'fromSeconds' => null, // center
        'style'       => ImageStyle::make('cover', 400, 300, LaruploadMediaStyle::CROP),
        'width'       => 400,
        'height'      => 300,
        'hash'        => ['94ca95920c56f3114bd20254a3774aa3', 'c2e8277e6fbfe6164c3627ccf5e02c77']
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

    $this->ffmpeg->capture(
        fromSeconds: 100,
        style: ImageStyle::make('cover', 400, 300, LaruploadMediaStyle::FIT),
        saveTo: $fileName
    );

    expect(file_exists($path))->toBeFalse();
});

it('wont capture screenshot if save-to path is not exist', function() {
    $fileName = 'not-exist/cover.jpg';
    $path = get_larupload_save_path('local', $fileName)['local'];

    $this->ffmpeg->capture(
        fromSeconds: 1,
        style: ImageStyle::make('cover', 400, 300, LaruploadMediaStyle::FIT),
        saveTo: $fileName
    );

    expect(file_exists($path))->toBeFalse();
})->throws(RuntimeException::class, 'Unable to save frame');

it('can guess dominant color during capturing process', function() {
    $color = $this->ffmpeg->capture(
        fromSeconds: 1,
        style: ImageStyle::make('cover', 400, 300, LaruploadMediaStyle::FIT),
        saveTo: 'cover.jpg',
        withDominantColor: true
    );

    expect($color)->toBe('#7a4e2a');
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
    [
        'style'  => VideoStyle::make('fit', 400, 300, LaruploadMediaStyle::FIT),
        'width'  => 400,
        'height' => 300,
    ],
    [
        'style'  => VideoStyle::make('auto', 400, 300, LaruploadMediaStyle::AUTO),
        'width'  => 400,
        'height' => 226,
    ],
    [
        'style'  => VideoStyle::make('scale-width', null, 300, LaruploadMediaStyle::SCALE_WIDTH),
        'width'  => 534,
        'height' => 300,
    ],
    [
        'style'  => VideoStyle::make('scale-height', 400, null, LaruploadMediaStyle::SCALE_HEIGHT),
        'width'  => 400,
        'height' => 226,
    ],
    [
        'style'  => VideoStyle::make('crop', 400, 300, LaruploadMediaStyle::CROP),
        'width'  => 400,
        'height' => 300,
    ],
    [
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
