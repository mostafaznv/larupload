<?php

use FFMpeg\Format\Video\WebM;
use Mostafaznv\Larupload\Actions\FixExceptionNamesAction;
use Mostafaznv\Larupload\DTOs\Style\ImageStyle;
use Mostafaznv\Larupload\DTOs\Style\VideoStyle;
use Mostafaznv\Larupload\Larupload;


it('returns the original name when no style is provided', function () {
    $res = FixExceptionNamesAction::make('example.svg', Larupload::ORIGINAL_FOLDER)->run();

    expect($res)->toBe('example.svg');
});

it('appends style extension when style is provided', function () {
    $style = VideoStyle::make('small', 400, 200, format: new WebM);
    $res = FixExceptionNamesAction::make('example.mp4', Larupload::ORIGINAL_FOLDER, $style)->run();

    expect($res)->toBe('example.webm');
});

it('wont change file extension if style doesnt have custom extension', function() {
    $path = 'path/to/file.png';
    $style = ImageStyle::make('custom-style', 100, 100);
    $res = FixExceptionNamesAction::make($path, $style->name, $style)->run();

    expect($res)->toBe($path);
});


it('replaces svg with jpg when style name is not original or cover', function () {
    $res = FixExceptionNamesAction::make('path/to/file.svg', 'custom-style')->run();

    expect($res)->toBe('path/to/file.jpg');
});

it('does not replace svg with jpg when style name is cover/original', function (string $folder) {
    $res = FixExceptionNamesAction::make('path/to/file.svg', $folder)->run();

    expect($res)->toBe('path/to/file.svg');

})->with([
    Larupload::ORIGINAL_FOLDER,
    Larupload::COVER_FOLDER,
]);


