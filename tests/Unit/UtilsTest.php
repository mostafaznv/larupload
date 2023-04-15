<?php

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use function Spatie\PestPluginTestTime\testTime;

it('will convert enum values to array', function() {
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

it('can check if driver is local or not', function() {
    $result = disk_driver_is_local('local');
    expect($result)->toBeTrue();

    $result = disk_driver_is_local('s3');
    expect($result)->toBeFalse();

    $result = disk_driver_is_local('sftp');
    expect($result)->toBeFalse();
});

it('can find temp dir', function() {
    $result = larupload_temp_dir();

    expect(is_dir($result))->toBeTrue();
});

it('can split path', function() {
    $result = split_larupload_path('/path/to/folder/file.png');

    expect($result)
        ->toBeArray()
        ->toHaveCount(2)
        ->toMatchArray([
            '/path/to/folder',
            'file.png',
        ]);
});

it('can generate save path', function() {
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

it('can upload file to remote disks', function() {
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

it('can upload folder to remote disks', function() {
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

it('can check if file is set and is an instance of uploaded-file', function() {
    $res = file_has_value('file');
    expect($res)->toBeFalse();

    $res = file_has_value(null);
    expect($res)->toBeFalse();

    $res = file_has_value(pdf());
    expect($res)->toBeTrue();
});

it('can check if instance of uploaded-file is valid or not', function() {
    $res = file_is_valid(null, 'file', 'cover');
    expect($res)->toBeTrue();

    $res = file_is_valid(pdf(), 'file', 'cover');
    expect($res)->toBeTrue();

    file_is_valid(png(2), 'file', 'cover');
})->throws(RuntimeException::class);
