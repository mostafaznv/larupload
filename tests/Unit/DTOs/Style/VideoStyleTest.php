<?php

use FFMpeg\Format\Audio\Aac;
use FFMpeg\Format\Audio\Flac;
use FFMpeg\Format\Audio\Mp3;
use FFMpeg\Format\Audio\Wav;
use FFMpeg\Format\Video\Ogg;
use FFMpeg\Format\Video\WebM;
use FFMpeg\Format\Video\X264;
use Mostafaznv\Larupload\DTOs\Style\VideoStyle;
use Mostafaznv\Larupload\Enums\LaruploadMediaStyle;


$cropOrFitDataset = [
    'd1' => [LaruploadMediaStyle::CROP, null, 100],
    'd2' => [LaruploadMediaStyle::CROP, 100, null],
    'd3' => [LaruploadMediaStyle::FIT, null, 100],
    'd4' => [LaruploadMediaStyle::FIT, 100, null]
];


it('will throw exception when name is numeric', function() {
    VideoStyle::make('12', 100, 100);

})->throws(Exception::class, 'Style name [12] is numeric. please use string name for your style');

it('will throw exception when mode is SCALE_HEIGHT but width is not set', function($width) {
    VideoStyle::make('test', $width, 100, LaruploadMediaStyle::SCALE_HEIGHT);

})->with([null, 0])->throws(Exception::class, 'Width is required when you are in SCALE_HEIGHT mode');

it('will throw exception when mode is SCALE_WIDTH but width is not set', function($height) {
    VideoStyle::make('test', 100, $height, LaruploadMediaStyle::SCALE_WIDTH);

})->with([null, 0])->throws(Exception::class, 'Height is required when you are in SCALE_WIDTH mode');

it('will check width and height both are required', function($mode, $width, $height) {
    VideoStyle::make('test', $width, $height, $mode);

})->with($cropOrFitDataset)->throws(Exception::class, 'Width and Height are required when you are in CROP/FIT mode');

it('will throw exception when mode is AUTO and both width and height are not set', function() {
    VideoStyle::make('test', null, null, LaruploadMediaStyle::AUTO);

})->throws(Exception::class, 'Width and height are required when you are in auto mode');

it('will return audio formats array correctly', function() {
    $style = VideoStyle::make('test', format: new Mp3);

    expect($style->audioFormats())
        ->toContain(Mp3::class)
        ->toContain(Aac::class)
        ->toContain(Wav::class)
        ->toContain(Flac::class);
});

it('will guess audio formats correctly', function(X264|WebM|Ogg|Mp3|Aac|Wav|Flac $format, bool $expected) {
    $style = VideoStyle::make('test', 300, 300, LaruploadMediaStyle::AUTO, $format);

    expect($style->isAudioFormat())->toBe($expected);

})->with([
    [new X264(), false],
    [new WebM(), false],
    [new Ogg(), false],

    [new Aac(), true],
    [new Wav(), true],
    [new Flac(), true],
    [new Mp3(), true],
]);

it('wont throw an dimension exception if format is audio', function(Mp3|Aac|Wav|Flac $format) {
    $style = VideoStyle::make('test', format: $format);

    expect($style)->toBeInstanceOf(VideoStyle::class);

})->with([
    new Mp3(),
    new Aac(),
    new Wav(),
    new Flac(),
]);

it('will set correct extensions for each video format', function(X264|WebM|Ogg|Mp3|Aac|Wav|Flac $format, string $expected) {
    $style = VideoStyle::make('test', 300, 300, LaruploadMediaStyle::AUTO, $format);

    expect($style->extension())->toBe($expected);

})->with([
    [new X264(), 'mp4'],
    [new WebM(), 'webm'],
    [new Ogg(), 'ogg'],

    [new Aac(), 'aac'],
    [new Wav(), 'wav'],
    [new Flac(), 'flac'],
    [new Mp3(), 'mp3'],
]);
