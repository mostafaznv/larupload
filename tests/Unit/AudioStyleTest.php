<?php

use FFMpeg\Format\Audio\Aac;
use FFMpeg\Format\Audio\Flac;
use FFMpeg\Format\Audio\Mp3;
use FFMpeg\Format\Audio\Wav;
use Mostafaznv\Larupload\DTOs\Style\AudioStyle;


it('will throw exception when name is numeric', function() {
    AudioStyle::make('12');

})->throws(Exception::class, 'Style name [12] is numeric. please use string name for your style');

it('will set correct extensions for each audio format', function(Aac|Wav|Flac|Mp3 $format, string $expected) {
    $style = AudioStyle::make('test', $format);

    expect($style->extension())->toBe($expected);

})->with([
    [new Aac(), 'aac'],
    [new Wav(), 'wav'],
    [new Flac(), 'flac'],
    [new Mp3(), 'mp3'],
]);
