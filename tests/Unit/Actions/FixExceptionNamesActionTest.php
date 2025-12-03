<?php

use FFMpeg\Format\Video\WebM;
use Mostafaznv\Larupload\Actions\FixExceptionNamesAction;
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

it('replaces svg with jpg when style name is not original or cover', function () {
    $res = FixExceptionNamesAction::make('example.svg', 'custom-style')->run();

    expect($res)->toBe('example.jpg');
});

it('does not replace svg with jpg when style name is cover/original', function (string $folder) {
    $res = FixExceptionNamesAction::make('example.svg', $folder)->run();

    expect($res)->toBe('example.svg');

})->with([
    Larupload::ORIGINAL_FOLDER,
    Larupload::COVER_FOLDER,
]);


