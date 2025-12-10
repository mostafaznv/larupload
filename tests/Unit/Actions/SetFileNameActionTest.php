<?php

use Mostafaznv\Larupload\Actions\SetFileNameAction;
use Mostafaznv\Larupload\Enums\LaruploadNamingMethod;
use Mostafaznv\Larupload\Test\Support\LaruploadTestConsts;
use function Spatie\PestPluginTestTime\testTime;



it('generates file name using hash method', function () {
    $result = SetFileNameAction::make(jpg(), LaruploadNamingMethod::HASH_FILE)->generate();

    expect($result)->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg-fa']['name']['hash']);
});

it('generates file name using time method', function () {
    testTime()->freeze('2024-01-01 12:00:00');

    $result = SetFileNameAction::make(jpg(), LaruploadNamingMethod::TIME)->generate();

    expect($result)->toBe('1704110400.jpg');
});

it('generates slugged file name with random number', function () {
    $result = SetFileNameAction::make(jpg(true), LaruploadNamingMethod::SLUG, 'fa')->generate();

    expect($result)->toStartWith(LaruploadTestConsts::IMAGE_DETAILS['jpg-fa']['name']['slug']);
});

