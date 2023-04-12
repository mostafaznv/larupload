<?php

use Mostafaznv\Larupload\DTOs\Style\ImageStyle;
use Mostafaznv\Larupload\Enums\LaruploadMediaStyle;

$cropOrFitDataset = [
    'd1' => [LaruploadMediaStyle::CROP, null, 100],
    'd2' => [LaruploadMediaStyle::CROP, 100, null],
    'd3' => [LaruploadMediaStyle::FIT, null, 100],
    'd4' => [LaruploadMediaStyle::FIT, 100, null]
];

it('will throw exception when name is numeric', function() {
    ImageStyle::make('12', 100, 100, LaruploadMediaStyle::SCALE_HEIGHT);

})->throws(Exception::class, 'Style name [12] is numeric. please use string name for your style');

it('will throw exception when mode is SCALE_HEIGHT but width is not set', function($width) {
    ImageStyle::make('test', $width, 100, LaruploadMediaStyle::SCALE_HEIGHT);

})->with([null, 0])->throws(Exception::class, 'Width is required when you are in SCALE_HEIGHT mode');

it('will throw exception when mode is SCALE_WIDTH but width is not set', function($height) {
    ImageStyle::make('test', 100, $height, LaruploadMediaStyle::SCALE_WIDTH);

})->with([null, 0])->throws(Exception::class, 'Height is required when you are in SCALE_WIDTH mode');

it('will check width and height both are required', function($mode, $width, $height) {
    ImageStyle::make('test', $width, $height, $mode);

})->with($cropOrFitDataset)->throws(Exception::class, 'Width and Height are required when you are in CROP/FIT mode');

it('will throw exception when mode is AUTO and both width and height are not set', function() {
    ImageStyle::make('test', null, null, LaruploadMediaStyle::AUTO);

})->throws(Exception::class, 'Width and height are required when you are in auto mode');
