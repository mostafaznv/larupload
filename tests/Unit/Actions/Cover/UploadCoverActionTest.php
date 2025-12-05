<?php

use Illuminate\Http\UploadedFile;
use Mostafaznv\Larupload\Actions\Cover\UploadCoverAction;
use Mostafaznv\Larupload\DTOs\CoverActionData;
use Mostafaznv\Larupload\DTOs\Style\ImageStyle;
use Mostafaznv\Larupload\Enums\LaruploadFileType;
use Mostafaznv\Larupload\DTOs\Style\Output;
use Mostafaznv\Larupload\Enums\LaruploadImageLibrary;
use Mostafaznv\Larupload\Enums\LaruploadMediaStyle;
use Mostafaznv\Larupload\Enums\LaruploadNamingMethod;
use Mostafaznv\Larupload\Storage\Image;
use Mostafaznv\Larupload\Test\Support\LaruploadTestConsts;
use function Spatie\PestPluginTestTime\testTime;


beforeEach(function () {
    $this->disk = 'public';

    Storage::fake($this->disk);


    $this->generate = function (bool $withDominantColor = false, LaruploadNamingMethod $namingMethod = LaruploadNamingMethod::HASH_FILE, ?ImageStyle $style = null, LaruploadFileType $type = LaruploadFileType::IMAGE) {
        if ($style === null) {
            $style = ImageStyle::make('small', 200, 200, LaruploadMediaStyle::CROP);
        }

        return CoverActionData::make(
            disk: $this->disk,
            namingMethod: $namingMethod,
            lang: 'en',
            style: $style,
            type: $type,
            generateCover: true,
            withDominantColor: $withDominantColor,
            dominantColorQuality: 10,
            imageProcessingLibrary: LaruploadImageLibrary::IMAGICK,
            output: Output::make()
        );
    };
});


it('will upload cover files using `HASH_FILE` naming methods', function () {
    # before
    $files = Storage::disk($this->disk)->allFiles();
    expect($files)->toBeEmpty();


    # action
    $data = ($this->generate)(false, LaruploadNamingMethod::HASH_FILE);
    $output = UploadCoverAction::make(jpg(), $data)->run('cover');
    $files = Storage::disk($this->disk)->allFiles();
    $hash = LaruploadTestConsts::IMAGE_DETAILS['jpg']['name']['hash'];


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

it('will upload cover files using `SLUG` naming methods', function () {
    # before
    $files = Storage::disk($this->disk)->allFiles();
    expect($files)->toBeEmpty();


    # action
    $data = ($this->generate)(false, LaruploadNamingMethod::SLUG);
    $output = UploadCoverAction::make(jpg(), $data)->run('cover');
    $files = Storage::disk($this->disk)->allFiles();
    $slug = LaruploadTestConsts::IMAGE_DETAILS['jpg']['name']['slug'];


    # test
    expect($output->cover)
        ->toMatch('/^' . preg_quote($slug, '/') . '-[0-9]{1,4}\.jpg$/')
        ->and($output->dominantColor)
        ->toBeNull()
        ->and($files)
        ->toBe([
            "cover/$output->cover",
        ]);
});

it('will upload cover files using `TIME` naming methods', function () {
    testTime()->freeze('2025-12-03 15:03:04');

    # before
    $files = Storage::disk($this->disk)->allFiles();
    expect($files)->toBeEmpty();


    # action
    $data = ($this->generate)(false, LaruploadNamingMethod::TIME);
    $output = UploadCoverAction::make(jpg(), $data)->run('cover');
    $files = Storage::disk($this->disk)->allFiles();
    $name = "1764774184.jpg";


    # test
    expect($output->cover)
        ->toBe($name)
        ->and($output->dominantColor)
        ->toBeNull()
        ->and($files)
        ->toBe([
            "cover/$name",
        ]);
});

it('can resize cover files', function (ImageStyle $style, int $width, int $height) {
    # action
    $data = ($this->generate)(false, LaruploadNamingMethod::HASH_FILE, $style);
    $output = UploadCoverAction::make(jpg(), $data)->run('cover');
    $files = Storage::disk($this->disk)->allFiles();
    $hash = LaruploadTestConsts::IMAGE_DETAILS['jpg']['name']['hash'];


    # test 1
    expect($output->cover)
        ->toBe($hash)
        ->and($output->dominantColor)
        ->toBeNull()
        ->and($files)
        ->toBe([
            "cover/$hash",
        ]);


    # test 2
    $path = Storage::disk($this->disk)->path("cover/$hash");
    $file = new UploadedFile($path, $hash, null, null, true);

    $image = new Image($file, $this->disk, LaruploadImageLibrary::GD);
    $meta = $image->getMeta();


    expect($meta)
        ->toHaveProperty('width', $width)
        ->toHaveProperty('height', $height);

})->with([
    'scale-width' => fn() => [
        'style'  => ImageStyle::make('fit', null, 100, LaruploadMediaStyle::SCALE_WIDTH),
        'width'  => 136,
        'height' => 100
    ],

    'scale-height' => fn() => [
        'style'  => ImageStyle::make('fit', 100, null, LaruploadMediaStyle::SCALE_HEIGHT),
        'width'  => 100,
        'height' => 73
    ],
]);

it('can calculate dominant color for not-image type uploads', function () {
    $data = ($this->generate)(true, LaruploadNamingMethod::HASH_FILE, null, LaruploadFileType::VIDEO);
    $output = UploadCoverAction::make(jpg(), $data)->run('cover');
    $files = Storage::disk($this->disk)->allFiles();
    $hash = LaruploadTestConsts::IMAGE_DETAILS['jpg']['name']['hash'];

    # test
    expect($output->cover)
        ->toBe($hash)
        ->and($output->dominantColor)
        ->toMatch(LaruploadTestConsts::HEX_REGEX)
        ->and($files)
        ->toBe([
            "cover/$hash",
        ]);
});

it('respects dominant-color flag', function () {
    $data = ($this->generate)(false, LaruploadNamingMethod::HASH_FILE, null, LaruploadFileType::VIDEO);
    $output = UploadCoverAction::make(jpg(), $data)->run('cover');
    $files = Storage::disk($this->disk)->allFiles();
    $hash = LaruploadTestConsts::IMAGE_DETAILS['jpg']['name']['hash'];

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
