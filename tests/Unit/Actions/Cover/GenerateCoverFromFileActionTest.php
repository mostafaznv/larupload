<?php

use Illuminate\Http\UploadedFile;
use Mostafaznv\Larupload\Actions\Cover\GenerateCoverFromFileAction;
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

    $this->generate = function (LaruploadFileType $type, bool $withDominantColor = false, string $format = 'png') {
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
                format: $format,
            )
        );
    };
});


it('wont process not white-listed file types', function (LaruploadFileType $type) {
    # before
    $files = Storage::disk($this->disk)->allFiles();
    expect($files)->toBeEmpty();


    # action
    $data = ($this->generate)($type);
    $output = GenerateCoverFromFileAction::make(png(), $data)->run('cover');
    $files = Storage::disk($this->disk)->allFiles();


    # test
    expect($output->cover)
        ->toBeNull()
        ->and($output->dominantColor)
        ->toBeNull()
        ->and($files)
        ->toBeEmpty();

})->with([
    //LaruploadFileType::IMAGE,
    //LaruploadFileType::VIDEO,
    LaruploadFileType::AUDIO,
    LaruploadFileType::DOCUMENT,
    LaruploadFileType::COMPRESSED,
    LaruploadFileType::FILE,
]);

it('can generate cover from video', function () {
    # before
    $files = Storage::disk($this->disk)->allFiles();
    expect($files)->toBeEmpty();


    # action
    $data = ($this->generate)(LaruploadFileType::VIDEO, format: 'mp4');
    $output = GenerateCoverFromFileAction::make(mp4(), $data)->run('cover');
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

it('can calculate dominant color during generating cover from videos', function () {
    $data = ($this->generate)(LaruploadFileType::VIDEO, true, 'mp4');
    $output = GenerateCoverFromFileAction::make(mp4(), $data)->run('cover');


    expect($output->dominantColor)->toMatch(LaruploadTestConsts::HEX_REGEX);
});

/*
 * svg -> png
 * other -> keep
 */
it('can generate cover from image', function (UploadedFile $file, $name) {
    # before
    $files = Storage::disk($this->disk)->allFiles();
    expect($files)->toBeEmpty();


    # action
    $format = $file->getClientOriginalExtension();
    $data = ($this->generate)(LaruploadFileType::IMAGE, format: $format);
    $output = GenerateCoverFromFileAction::make($file, $data)->run('cover');
    $files = Storage::disk($this->disk)->allFiles();


    # test
    expect($output->cover)
        ->toBe($name)
        ->and($output->dominantColor)
        ->toBeNull()
        ->and($files)
        ->toBe([
            "cover/$name",
        ]);

})->with([
    fn() => [png(), 'generated-cover.png'],
    fn() => [jpg(), 'generated-cover.jpg'],
    fn() => [webp(), 'generated-cover.webp'],
    fn() => [svg(), 'generated-cover.png'], # svg converted to png
]);

it('wont calculate dominant color during generating cover from images', function () {
    $data = ($this->generate)(LaruploadFileType::IMAGE, true, 'png');
    $output = GenerateCoverFromFileAction::make(png(), $data)->run('cover');

    expect($output->cover)
        ->toBe('generated-cover.png')
        ->and($output->dominantColor)
        ->toBeNull();
});
