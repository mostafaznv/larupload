<?php

use Mostafaznv\Larupload\Actions\GuessLaruploadFileTypeAction;
use Mostafaznv\Larupload\Enums\LaruploadFileType;

it('can guess image type', function() {
    $action = GuessLaruploadFileTypeAction::make(jpg());

    $result = $action->calc();
    expect($result)->toBe(LaruploadFileType::IMAGE);

    $result = $action->isImage();
    expect($result)->toBeTrue();
});

it('can guess video type', function() {
    $result = GuessLaruploadFileTypeAction::make(mp4())->calc();

    expect($result)->toBe(LaruploadFileType::VIDEO);
});

it('can guess audio type', function() {
    $result = GuessLaruploadFileTypeAction::make(mp3())->calc();

    expect($result)->toBe(LaruploadFileType::AUDIO);
});

it('can guess document type', function() {
    $result = GuessLaruploadFileTypeAction::make(pdf())->calc();

    expect($result)->toBe(LaruploadFileType::DOCUMENT);
});

it('can guess compress type', function() {
    $result = GuessLaruploadFileTypeAction::make(zip())->calc();

    expect($result)->toBe(LaruploadFileType::COMPRESSED);
});

it('can guess other type', function() {
    $result = GuessLaruploadFileTypeAction::make(php())->calc();

    expect($result)->toBe(LaruploadFileType::FILE);
});

it("can't guess file type if file is invalid", function() {
    $result = GuessLaruploadFileTypeAction::make(png(2))->calc();

    expect($result)->toBeNull();
});
