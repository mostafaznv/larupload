<?php

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Mostafaznv\Larupload\DTOs\Style\Output;
use Mostafaznv\Larupload\Enums\LaruploadMode;
use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Storage\Attachment;
use function Spatie\PestPluginTestTime\testTime;

it('will convert enum values to array', function () {
    enum TestEnum
    {
        case CASE1;
        case CASE2;
    }

    $result = enum_to_names(TestEnum::cases());

    expect($result)
        ->toBeArray()
        ->toMatchArray(['CASE1', 'CASE2']);
});

it('can check if driver is local or not', function () {
    $result = disk_driver_is_local('local');
    expect($result)->toBeTrue();

    $result = disk_driver_is_local('s3');
    expect($result)->toBeFalse();

    $result = disk_driver_is_local('sftp');
    expect($result)->toBeFalse();
});

it('can find temp dir', function () {
    $result = larupload_temp_dir();

    expect(is_dir($result))->toBeTrue();
});

it('can split path', function () {
    $result = split_larupload_path('/path/to/folder/file.png');

    expect($result)
        ->toBeArray()
        ->toHaveCount(2)
        ->toMatchArray([
            '/path/to/folder',
            'file.png',
        ]);
});

it('can generate save path', function () {
    $path = 'path/to/file.png';
    $localPath = config('filesystems.disks.local.root');

    $result = get_larupload_save_path('local', $path);

    expect($result)
        ->toBeArray()
        ->toHaveCount(5)
        ->toMatchArray([
            'path'      => 'path/to',
            'name'      => 'file.png',
            'temp'      => null,
            'local'     => $localPath . '/' . $path,
            'permanent' => $localPath . '/' . $path,
        ]);


    $carbon = Carbon::createFromFormat('Y-m-d H:i:s', '1990-09-20 07:10:48');
    $time = $carbon->unix();
    testTime()->freeze($carbon);

    $result = get_larupload_save_path('s3', $path);

    $tempPath = larupload_temp_dir();

    expect($result)
        ->toBeArray()
        ->toHaveCount(5)
        ->toMatchArray([
            'path'      => 'path/to',
            'name'      => 'file.png',
            'temp'      => $tempPath . "/$time-file.png",
            'local'     => $tempPath . "/$time-file.png",
            'permanent' => 'path/to/file.png',
        ]);
});

it('can generate save path with custom extension', function () {
    $path = 'path/to/file.png';
    $newPath = 'path/to/file.jpg';
    $localPath = config('filesystems.disks.local.root');

    $result = get_larupload_save_path('local', $path, 'jpg');

    expect($result)
        ->toBeArray()
        ->toHaveCount(5)
        ->toMatchArray([
            'path'      => 'path/to',
            'name'      => 'file.jpg',
            'temp'      => null,
            'local'     => $localPath . '/' . $newPath,
            'permanent' => $localPath . '/' . $newPath,
        ]);


    $carbon = Carbon::createFromFormat('Y-m-d H:i:s', '1990-09-20 07:10:48');
    $time = $carbon->unix();
    testTime()->freeze($carbon);

    $result = get_larupload_save_path('s3', $path, 'jpg');

    $tempPath = larupload_temp_dir();

    expect($result)
        ->toBeArray()
        ->toHaveCount(5)
        ->toMatchArray([
            'path'      => 'path/to',
            'name'      => 'file.jpg',
            'temp'      => $tempPath . "/$time-file.jpg",
            'local'     => $tempPath . "/$time-file.jpg",
            'permanent' => $newPath,
        ]);
});

it('can upload file to remote disks', function () {
    $driver = 's3';
    $file = pdf();
    $path = 'path/to/' . $file->getClientOriginalName();

    Storage::fake($driver);

    $saveTo = get_larupload_save_path($driver, $path);
    copy($file->getRealPath(), $saveTo['temp']);

    larupload_finalize_save($driver, $saveTo);

    $files = Storage::disk($driver)->allFiles();

    expect($files)
        ->toBeArray()
        ->toHaveCount(1)
        ->toMatchArray([
            $path
        ])
        ->and(file_exists($saveTo['temp']))
        ->toBeFalse();
});

it('can upload folder to remote disks', function () {
    $driver = 's3';
    $path = 'path/to/hls';

    Storage::fake($driver);

    $saveTo = get_larupload_save_path($driver, $path);
    $paths = [
        [
            'source' => pdf()->getRealPath(),
            'target' => $saveTo['temp'] . '/file.pdf'
        ],
        [
            'source' => zip()->getRealPath(),
            'target' => $saveTo['temp'] . '/file.zip'
        ],
    ];

    mkdir($saveTo['local']);
    foreach ($paths as $p) {
        copy($p['source'], $p['target']);
    }

    larupload_finalize_save($driver, $saveTo, true);

    $files = Storage::disk($driver)->allFiles();

    expect($files)
        ->toBeArray()
        ->toHaveCount(2)
        ->toMatchArray([
            $path . '/file.pdf',
            $path . '/file.zip',
        ])
        ->and(file_exists($paths[0]['target']))
        ->toBeFalse()
        ->and(file_exists($paths[1]['target']))
        ->toBeFalse()
        ->and(is_dir($saveTo['local']))
        ->toBeFalse();
});

it('can check if file is set and is an instance of uploaded-file', function () {
    $res = file_has_value('file');
    expect($res)->toBeFalse();

    $res = file_has_value(null);
    expect($res)->toBeFalse();

    $res = file_has_value(pdf());
    expect($res)->toBeTrue();
});

it('can check if instance of uploaded-file is valid or not', function () {
    $res = file_is_valid(null, 'file', 'cover');
    expect($res)->toBeTrue();

    $res = file_is_valid(pdf(), 'file', 'cover');
    expect($res)->toBeTrue();

    file_is_valid(png(2), 'file', 'cover');
})->throws(RuntimeException::class);


# larupload-style-path
it('will return path unchanged if extension is null', function () {
    $original = 'path/to/file.mp3';
    $path = larupload_style_path($original, null);

    expect($path)->toBe($original);
});

it('will return path unchanged if extension is an empty string', function () {
    $original = 'path/to/file.mp3';
    $path = larupload_style_path($original, '');

    expect($path)->toBe($original);
});

it('will change path using the given extension', function () {
    $path = larupload_style_path('path/to/file.mp3', 'wav');

    expect($path)->toBe('path/to/file.wav');
});

it('trims the extra dot at the beginning of the path when dirname is null', function () {
    $path = larupload_style_path('file.mp3', 'wav');

    expect($path)->toBe('file.wav');
});


# larupload-relative-path
it('generates relative path in standalone mode without folder', function () {
    $attachment = Attachment::make('example_file', LaruploadMode::STANDALONE);
    $attachment->folder = 'uploads';
    $attachment->nameKebab = 'example-file';

    $result = larupload_relative_path($attachment, '123');

    expect($result)->toBe('uploads/example-file');
});

it('generates relative path in standalone mode with folder', function () {
    $attachment = Attachment::make('example_file', LaruploadMode::STANDALONE);
    $attachment->folder = 'uploads';
    $attachment->nameKebab = 'example-file';

    $result = larupload_relative_path($attachment, '123', 'custom_folder');

    expect($result)->toBe('uploads/example-file/custom-folder');
});

it('generates relative path in non-standalone mode without folder', function () {
    $attachment = Attachment::make('example_file');
    $attachment->folder = 'uploads';
    $attachment->nameKebab = 'example-file';

    $result = larupload_relative_path($attachment, '123');

    expect($result)->toBe('uploads/123/example-file');
});

it('generates relative path in non-standalone mode with folder', function () {
    $attachment = Attachment::make('example_file');
    $attachment->folder = 'uploads';
    $attachment->nameKebab = 'example-file';

    $result = larupload_relative_path($attachment, '123', 'custom_folder');

    expect($result)->toBe('uploads/123/example-file/custom-folder');
});

it('trims slashes from folder and path', function () {
    $attachment = Attachment::make('example_file');
    $attachment->folder = 'uploads';
    $attachment->nameKebab = 'example-file/';

    $result = larupload_relative_path($attachment, '123');

    expect($result)->toBe('uploads/123/example-file');
});

it('trims slashes from folder and path with folder', function () {
    $attachment = Attachment::make('example_file');
    $attachment->folder = 'uploads';
    $attachment->nameKebab = 'example-file/';

    $result = larupload_relative_path($attachment, '123', 'folder/');

    expect($result)->toBe('uploads/123/example-file/folder');
});


# local copy
it('returns false when the disk is local', function () {
    Storage::fake('local');

    $attachment = Attachment::make('example_file');
    $attachment->disk = 'local';
    $attachment->localDisk = 'local';
    $attachment->folder = 'uploads';
    $attachment->nameKebab = 'example-file';
    $attachment->id = '123';
    $attachment->file = pdf();
    $attachment->output = Output::make(name: 'file.pdf');

    $result = local_copy($attachment);

    expect($result)
        ->toBeFalse()
        ->and(Storage::disk('local')->allFiles())
        ->toBeEmpty();
});

it('creates a local copy for remote disks and returns true', function () {
    Storage::fake('local');
    Storage::fake('s3');

    $attachment = Attachment::make('example_file');
    $attachment->disk = 's3';
    $attachment->localDisk = 'local';
    $attachment->folder = 'uploads';
    $attachment->nameKebab = 'example-file';
    $attachment->id = '123';
    $attachment->file = pdf();
    $attachment->output = Output::make(name: 'file.pdf');

    $path = larupload_relative_path($attachment, $attachment->id, Larupload::ORIGINAL_FOLDER);

    $result = local_copy($attachment);

    expect($result)
        ->toBeTrue()
        ->and(Storage::disk('local')->exists("$path/file.pdf"))
        ->toBeTrue()
        ->and(Storage::disk('local')->get("$path/file.pdf"))
        ->toBe(file_get_contents($attachment->file->getRealPath()));
});

it('returns true when a local copy already exists', function () {
    Storage::fake('local');
    Storage::fake('s3');

    $attachment = Attachment::make('example_file');
    $attachment->disk = 's3';
    $attachment->localDisk = 'local';
    $attachment->folder = 'uploads';
    $attachment->nameKebab = 'example-file';
    $attachment->id = '123';
    $attachment->file = pdf();
    $attachment->output = Output::make(name: 'file.pdf');

    $path = larupload_relative_path($attachment, $attachment->id, Larupload::ORIGINAL_FOLDER);
    Storage::disk('local')->put("$path/file.pdf", 'existing-content');

    $result = local_copy($attachment);

    expect($result)
        ->toBeTrue()
        ->and(Storage::disk('local')->get("$path/file.pdf"))
        ->toBe('existing-content');
});

it('returns false when `putFileAs` fails', function () {
    $attachment = Attachment::make('example_file');
    $attachment->disk = 's3';
    $attachment->localDisk = 'local';
    $attachment->folder = 'uploads';
    $attachment->nameKebab = 'example-file';
    $attachment->id = '123';
    $attachment->file = pdf();
    $attachment->output = Output::make(name: 'file.pdf');

    $storage = Mockery::mock();
    $storage->shouldReceive('exists')->once()->andReturnFalse();
    $storage->shouldReceive('putFileAs')->once()->andReturnFalse();

    $lock = Mockery::mock();
    $lock->shouldReceive('block')
        ->once()
        ->with(5, Mockery::type(Closure::class))
        ->andReturnUsing(function (int $seconds, Closure $callback) {
            return $callback();
        });

    Storage::shouldReceive('disk')
        ->once()
        ->with('local')
        ->andReturn($storage);

    Cache::shouldReceive('lock')
        ->once()
        ->andReturn($lock);

    $result = local_copy($attachment);

    expect($result)->toBeFalse();
});


# delete local copy
it('returns false when the disk is local for delete_local_copy', function () {
    Storage::fake('local');

    $attachment = Attachment::make('example_file');
    $attachment->disk = 'local';
    $attachment->localDisk = 'local';
    $attachment->folder = 'uploads';
    $attachment->nameKebab = 'example-file';
    $attachment->id = '123';
    $attachment->file = pdf();
    $attachment->output = Output::make(name: 'file.pdf');

    $result = delete_local_copy($attachment);

    expect($result)->toBeFalse();
});

it('returns false when cache key is missing in delete_local_copy', function () {
    $attachment = Attachment::make('example_file');
    $attachment->disk = 's3';
    $attachment->localDisk = 'local';
    $attachment->folder = 'uploads';
    $attachment->nameKebab = 'example-file';
    $attachment->id = '123';
    $attachment->file = pdf();
    $attachment->output = Output::make(name: 'file.pdf');

    $result = delete_local_copy($attachment);

    expect($result)->toBeFalse();
});

it('forgets cache and deletes directory when cache value is <= 1', function () {
    $attachment = Attachment::make('example_file');
    $attachment->disk = 's3';
    $attachment->localDisk = 'local';
    $attachment->folder = 'uploads';
    $attachment->nameKebab = 'example-file';
    $attachment->id = '123';
    $attachment->file = pdf();
    $attachment->output = Output::make(name: 'file.pdf');

    $path = larupload_relative_path($attachment, $attachment->id, Larupload::ORIGINAL_FOLDER);
    $fullPath = "$path/{$attachment->output->name}";
    $md5 = md5("$attachment->localDisk/$fullPath");
    $cacheKey = "larupload:$md5";

    Cache::increment($cacheKey);

    $result = delete_local_copy($attachment);

    expect($result)->toBeTrue();
});

it('decrements cache and returns false when cache value is greater than 1', function () {
    $attachment = Attachment::make('example_file');
    $attachment->disk = 's3';
    $attachment->localDisk = 'local';
    $attachment->folder = 'uploads';
    $attachment->nameKebab = 'example-file';
    $attachment->id = '123';
    $attachment->file = pdf();
    $attachment->output = Output::make(name: 'file.pdf');

    $path = larupload_relative_path($attachment, $attachment->id, Larupload::ORIGINAL_FOLDER);
    $fullPath = "$path/{$attachment->output->name}";
    $md5 = md5("$attachment->localDisk/$fullPath");
    $cacheKey = "larupload:$md5";

    Cache::increment($cacheKey, 5);

    $result = delete_local_copy($attachment);
    expect($result)->toBeFalse();


    $value = Cache::get($cacheKey);
    expect($value)->toBe(4);
});

