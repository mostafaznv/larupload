<?php

use FFMpeg\Format\Video\X264;
use Mostafaznv\Larupload\DTOs\Style\StreamStyle;

$nameDataset = [
    '12', 12, 'n@me', 'stream name', 'stream_name', 'نام'
];

it('will throw exception when name is not alpha-numeric', function($name) {
    StreamStyle::make($name, 100, 100, new X264());

})->with($nameDataset)->throws(Exception::class);

it('will throw exception when width is not a positive number', function() {
    StreamStyle::make('test', -12, 100, new X264());

})->throws(Exception::class, 'width [-12] should be a positive number');

it('will throw exception when height is not a positive number', function() {
    StreamStyle::make('test', 100, -12, new X264());

})->throws(Exception::class, 'height [-12] should be a positive number');
