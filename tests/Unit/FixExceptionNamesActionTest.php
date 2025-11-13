<?php

use FFMpeg\Format\Audio\Aac;
use Mostafaznv\Larupload\Actions\FixExceptionNamesAction;
use Mostafaznv\Larupload\DTOs\Style\AudioStyle;
use Mostafaznv\Larupload\DTOs\Style\ImageStyle;
use Mostafaznv\Larupload\Larupload;


it('wont convert svg to jpg for original/cover styles', function(string $style) {
    $path = 'path/to/file.svg';

    $res = FixExceptionNamesAction::make($path, $style)->run();

    expect($res)->toBe($path);

})->with([
    Larupload::ORIGINAL_FOLDER,
    Larupload::COVER_FOLDER
]);

it('will convert svg to jpg for custom styles', function() {
    $res = FixExceptionNamesAction::make('path/to/file.svg', 'custom-style')->run();

    expect($res)->toBe('path/to/file.jpg');
});

it('will change file extension based on style', function() {
    $style = AudioStyle::make('custom-style', new Aac());
    $res = FixExceptionNamesAction::make('path/to/file.mp3', $style->name, $style)->run();

    expect($res)->toBe('path/to/file.aac');
});

it('wont change file extension if style doesnt have custom extension', function() {
    $style = ImageStyle::make('custom-style', 100, 100);
    $path = 'path/to/file.png';
    $res = FixExceptionNamesAction::make($path, $style->name, $style)->run();

    expect($res)->toBe($path);
});

