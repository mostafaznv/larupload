<?php

use Illuminate\Http\UploadedFile;
use Mostafaznv\Larupload\Actions\GuessLaruploadFileTypeAction;
use Mostafaznv\Larupload\Enums\LaruploadFileType;


it('returns null if the file is not valid', function () {
    $file = new UploadedFile(mp3()->getRealPath(), 'audio.mp3', null, 2, true);
    expect($file->isValid())->toBeFalse();


    $res = GuessLaruploadFileTypeAction::make($file)->calc();
    expect($res)->toBeNull();
});

it('returns correct file type for valid image file', function (UploadedFile $file) {
    $res = GuessLaruploadFileTypeAction::make($file)->calc();
    expect($res)->toBe(LaruploadFileType::IMAGE);

})->with([
    'png'    => fn() => png(),
    'jpg'    => fn() => jpg(),
    'jpg-fa' => fn() => jpg(true),
    'webp'   => fn() => webp(),
    'svg'    => fn() => svg(),
    'gif'    => fn() => gif(),
]);

it('returns correct file type for valid video file', function () {
    $res = GuessLaruploadFileTypeAction::make(mp4())->calc();

    expect($res)->toBe(LaruploadFileType::VIDEO);
});

it('returns correct file type for valid audio file', function () {
    $res = GuessLaruploadFileTypeAction::make(mp3())->calc();

    expect($res)->toBe(LaruploadFileType::AUDIO);
});

it('returns correct file type for valid document file', function (string $mimeType) {
    $file = Mockery::mock(UploadedFile::class);
    $file->shouldReceive('isValid')->andReturn(true);
    $file->shouldReceive('getMimeType')->andReturn($mimeType);

    $res = GuessLaruploadFileTypeAction::make($file)->calc();

    expect($res)->toBe(LaruploadFileType::DOCUMENT);

})->with([
    'application/pdf',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'application/rtf',
    'application/xhtml+xml',
    'text/xml',
    'application/msword',
]);

it('returns correct file type for valid compressed file', function (string $mimeType) {
    $file = Mockery::mock(UploadedFile::class);
    $file->shouldReceive('isValid')->andReturn(true);
    $file->shouldReceive('getMimeType')->andReturn($mimeType);

    $res = GuessLaruploadFileTypeAction::make($file)->calc();

    expect($res)->toBe(LaruploadFileType::COMPRESSED);

})->with([
    'application/zip',
    'application/x-tar',
    'application/x-compress',
    'application/x-bzip-compressed-tar',
]);

it('returns generic file type for unknown mime type', function () {
    $file = Mockery::mock(UploadedFile::class);
    $file->shouldReceive('isValid')->andReturn(true);
    $file->shouldReceive('getMimeType')->andReturn('application/x-unknown');

    $res = GuessLaruploadFileTypeAction::make($file)->calc();

    expect($res)->toBe(LaruploadFileType::FILE);
});

it('identifies image file correctly', function () {
    $res = GuessLaruploadFileTypeAction::make(png())->isImage();

    expect($res)->toBeTrue();
});

it('does not identify non-image file as image', function () {
    $res = GuessLaruploadFileTypeAction::make(mp3())->isImage();

    expect($res)->toBeFalse();
});

it('can guess image type', function () {
    $action = GuessLaruploadFileTypeAction::make(jpg());

    $result = $action->calc();
    expect($result)->toBe(LaruploadFileType::IMAGE);

    $result = $action->isImage();
    expect($result)->toBeTrue();
});

it('can guess video type', function () {
    $result = GuessLaruploadFileTypeAction::make(mp4())->calc();

    expect($result)->toBe(LaruploadFileType::VIDEO);
});

it('can guess audio type', function () {
    $result = GuessLaruploadFileTypeAction::make(mp3())->calc();

    expect($result)->toBe(LaruploadFileType::AUDIO);
});

it('can guess document type', function () {
    $result = GuessLaruploadFileTypeAction::make(pdf())->calc();

    expect($result)->toBe(LaruploadFileType::DOCUMENT);
});

it('can guess compress type', function () {
    $result = GuessLaruploadFileTypeAction::make(zip())->calc();

    expect($result)->toBe(LaruploadFileType::COMPRESSED);
});

it('can guess other type', function () {
    $result = GuessLaruploadFileTypeAction::make(php())->calc();

    expect($result)->toBe(LaruploadFileType::FILE);
});

it("can't guess file type if file is invalid", function () {
    $result = GuessLaruploadFileTypeAction::make(png(2))->calc();

    expect($result)->toBeNull();
});

it('all types of file are retrievable using enum', function (string $name, LaruploadFileType $enum) {
    $result = LaruploadFileType::from($name);

    expect($result)->toBe($enum);

})->with([
    ['IMAGE', LaruploadFileType::IMAGE],
    ['VIDEO', LaruploadFileType::VIDEO],
    ['AUDIO', LaruploadFileType::AUDIO],
    ['DOCUMENT', LaruploadFileType::DOCUMENT],
    ['COMPRESSED', LaruploadFileType::COMPRESSED],
    ['FILE', LaruploadFileType::FILE],
]);
