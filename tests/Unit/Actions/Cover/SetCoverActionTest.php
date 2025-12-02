<?php

use Illuminate\Http\UploadedFile;
use Mostafaznv\Larupload\Actions\Cover\GenerateCoverFromFileAction;
use Mostafaznv\Larupload\Actions\Cover\SetCoverAction;
use Mostafaznv\Larupload\DTOs\CoverActionData;
use Mostafaznv\Larupload\DTOs\Style\ImageStyle;
use Mostafaznv\Larupload\Enums\LaruploadFileType;
use Mostafaznv\Larupload\DTOs\Style\Output;
use Mostafaznv\Larupload\Enums\LaruploadImageLibrary;
use Mostafaznv\Larupload\Enums\LaruploadMediaStyle;
use Mostafaznv\Larupload\Enums\LaruploadNamingMethod;
use Mostafaznv\Larupload\Test\Support\LaruploadTestConsts;


beforeEach(function () {
    $this->disk = 'public';

    Storage::fake($this->disk);


    $this->generate = function (LaruploadFileType $type = LaruploadFileType::IMAGE, bool $withDominantColor = false) {
        return CoverActionData::make(
            disk: $this->disk,
            namingMethod: LaruploadNamingMethod::HASH_FILE,
            lang: 'en',
            style: ImageStyle::make('small', 200, 200, LaruploadMediaStyle::CROP),
            type: $type,
            generateCover: true,
            withDominantColor: $withDominantColor,
            dominantColorQuality: 10,
            imageProcessingLibrary: LaruploadImageLibrary::IMAGICK,
            output: Output::make(
                name: 'generated-cover.png',
                type: LaruploadFileType::IMAGE,
                format: 'png',
            )
        );
    };
});


it('will upload cover files when the cover file is a type of image', function () {
    # before
    $files = Storage::disk($this->disk)->allFiles();
    expect($files)->toBeEmpty();


    # action
    $data = ($this->generate)();
    $output = SetCoverAction::make(jpg(), png(), $data)->run('cover');
    $files = Storage::disk($this->disk)->allFiles();
    $hash = LaruploadTestConsts::IMAGE_DETAILS['png']['name']['hash'];


    # test
    expect($output->cover)
        ->toBe($hash)
        ->and($output->dominantColor)
        ->toBeNull()
        ->and($files)
        ->toBe([
            "cover/$hash",
        ]);

});

it('wont upload cover files when the cover file is not a type of image', function () {
    # before
    $files = Storage::disk($this->disk)->allFiles();
    expect($files)->toBeEmpty();


    # action
    $data = ($this->generate)();
    $output = SetCoverAction::make(jpg(), pdf(), $data)->run('cover');
    $files = Storage::disk($this->disk)->allFiles();


    # test
    expect($output->cover)
        ->toBe('generated-cover.png')
        ->and($output->dominantColor)
        ->toBeNull()
        ->and($files)
        ->toBe([
            'cover/generated-cover.png',
        ]);

});

it('will automatically generate a cover when the cover file is null', function () {
    # before
    $files = Storage::disk($this->disk)->allFiles();
    expect($files)->toBeEmpty();


    # action
    $data = ($this->generate)();
    $output = SetCoverAction::make(png(), null, $data)->run('cover');
    $files = Storage::disk($this->disk)->allFiles();

    # test
    expect($output->cover)
        ->toBe('generated-cover.png')
        ->and($output->dominantColor)
        ->toBeNull()
        ->and($files)
        ->toBe([
            'cover/generated-cover.png',
        ]);
});

it('will automatically generate a cover from video files', function () {
    # before
    $files = Storage::disk($this->disk)->allFiles();
    expect($files)->toBeEmpty();


    # action
    $data = ($this->generate)(LaruploadFileType::VIDEO);
    $output = SetCoverAction::make(mp4(), null, $data)->run('cover');
    $files = Storage::disk($this->disk)->allFiles();


    # test
    expect($output->cover)
        ->toBe('generated-cover.jpg')
        ->and($output->dominantColor)
        ->toBeNull()
        ->and($files)
        ->toBe([
            'cover/generated-cover.jpg',
        ]);
});

it('can calculate dominant-color during generating covers from video files', function () {
    # action
    $data = ($this->generate)(LaruploadFileType::VIDEO, true);
    $output = SetCoverAction::make(mp4(), null, $data)->run('cover');


    # test
    expect($output->dominantColor)->toMatch(LaruploadTestConsts::HEX_REGEX);
});

it('wont calculate dominant-color during generating covers from video images', function () {
    # action
    $data = ($this->generate)(LaruploadFileType::IMAGE, true);
    $output = SetCoverAction::make(png(), null, $data)->run('cover');


    # test
    expect($output->cover)
        ->toBe('generated-cover.png')
        ->and($output->dominantColor)
        ->toBeNull();
});

it('wont generate a cover for non image files', function () {
    # before
    $files = Storage::disk($this->disk)->allFiles();
    expect($files)->toBeEmpty();


    # action
    $data = ($this->generate)(LaruploadFileType::DOCUMENT);
    $output = SetCoverAction::make(pdf(), null, $data)->run('cover');
    $files = Storage::disk($this->disk)->allFiles();

    # test
    expect($output->cover)
        ->toBeNull()
        ->and($output->dominantColor)
        ->toBeNull()
        ->and($files)
        ->toBeEmpty();
});

it('will delete the existing cover when the cover is set to false', function () {
    # prepare
    Storage::disk($this->disk)->put('cover/existing-cover.png', 'test-file');

    # before
    $files = Storage::disk($this->disk)->allFiles();
    expect($files)->toBe([
        'cover/existing-cover.png',
    ]);


    # action
    $data = ($this->generate)();
    $output = SetCoverAction::make(jpg(), false, $data)->run('cover');
    $files = Storage::disk($this->disk)->allFiles();

    # test
    expect($output->cover)
        ->toBeNull()
        ->and($output->dominantColor)
        ->toBeNull()
        ->and($files)
        ->toBeEmpty();
});

