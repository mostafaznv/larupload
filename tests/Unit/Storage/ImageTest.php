<?php

use Illuminate\Http\UploadedFile;
use Mostafaznv\Larupload\DTOs\Image\ImageMeta;
use Mostafaznv\Larupload\DTOs\Style\ImageStyle;
use Mostafaznv\Larupload\Enums\LaruploadImageLibrary;
use Mostafaznv\Larupload\Enums\LaruploadMediaStyle;
use Mostafaznv\Larupload\Storage\Image;
use Illuminate\Support\Facades\Storage;
use Mostafaznv\Larupload\Test\Support\LaruploadTestConsts;


beforeEach(function() {
    $this->image = new Image(jpg(), 'local', LaruploadImageLibrary::GD, 10);
});


function resize(UploadedFile $file, LaruploadImageLibrary $library, int $width = 100, int $height = 73): void
{
    $disk = 'local';
    $saveTo = 'image.jpg';
    $path = Storage::disk($disk)->path($saveTo);

    $image = new Image($file, $disk, $library, 10);
    $image->resize(
        saveTo: $saveTo,
        style: ImageStyle::make('fit', 100, 100)
    );

    expect(file_exists($path))->toBeTrue();

    $file = new UploadedFile($path, $saveTo, null, null, true);
    $image = new Image($file, $disk, $library, 10);
    $meta = $image->getMeta();

    expect($meta)
        ->toHaveProperty('width', $width)
        ->toHaveProperty('height', $height);
}

function dominant(UploadedFile $file, LaruploadImageLibrary $library, string $expected): void
{
    $image = new Image($file, 'local', $library, 10);
    $color = $image->getDominantColor();

    expect($color)->toBe($expected);
}


it('can get meta of image file', function() {
    $meta = $this->image->getMeta();

    expect($meta)
        ->toBeInstanceOf(ImageMeta::class)
        ->toHaveProperty('width', 1077)
        ->toHaveProperty('height', 791);
});

it('can resize image file', function(UploadedFile $file, ImageStyle $style, int $width, int $height) {
    $disk = 'local';
    $saveTo = 'image.jpg';
    $path = Storage::disk($disk)->path($saveTo);

    $image = new Image($file, $disk, LaruploadImageLibrary::GD, 10);
    $image->resize($saveTo, $style);

    expect(file_exists($path))->toBeTrue();

    $file = new UploadedFile($path, $saveTo, null, null, true);
    $image = new Image($file, $disk, LaruploadImageLibrary::GD, 10);
    $meta = $image->getMeta();


    expect($meta)
        ->toHaveProperty('width', $width)
        ->toHaveProperty('height', $height);

})->with([
    'fit' => fn() => [
        'file'   => jpg(),
        'style'  => ImageStyle::make('fit', 100, 100, LaruploadMediaStyle::FIT),
        'width'  => 100,
        'height' => 100
    ],
    'auto-1' => fn() => [
        'file'   => jpg(),
        'style'  => ImageStyle::make('fit', 100, 100, LaruploadMediaStyle::AUTO),
        'width'  => 100,
        'height' => 73
    ],
    'scale-width' => fn() => [
        'file'   => jpg(),
        'style'  => ImageStyle::make('fit', null, 100, LaruploadMediaStyle::SCALE_WIDTH),
        'width'  => 136,
        'height' => 100
    ],
    'scale-height' => fn() => [
        'file'   => jpg(),
        'style'  => ImageStyle::make('fit', 100, null, LaruploadMediaStyle::SCALE_HEIGHT),
        'width'  => 100,
        'height' => 73
    ],
    'crop' => fn() => [
        'file'   => jpg(),
        'style'  => ImageStyle::make('fit', 100, 100, LaruploadMediaStyle::CROP),
        'width'  => 100,
        'height' => 100
    ],
    'auto-2' => fn() => [
        'file'   => jpg(),
        'style'  => ImageStyle::make('fit', 100, null, LaruploadMediaStyle::AUTO),
        'width'  => 100,
        'height' => 73
    ],
    'auto-3' => fn() => [
        'file'   => jpg(),
        'style'  => ImageStyle::make('fit', null, 100, LaruploadMediaStyle::AUTO),
        'width'  => 1077,
        'height' => 791
    ],
    'auto-4' => fn() => [
        'file'   => squareImage(),
        'style'  => ImageStyle::make('fit', 101, 100, LaruploadMediaStyle::AUTO),
        'width'  => 101,
        'height' => 101
    ],
    'auto-5' => fn() => [
        'file'   => squareImage(),
        'style'  => ImageStyle::make('fit', 105, 106, LaruploadMediaStyle::AUTO),
        'width'  => 106,
        'height' => 106
    ],
    'auto-6' => fn() => [
        'file'   => squareImage(),
        'style'  => ImageStyle::make('fit', 50, 50, LaruploadMediaStyle::AUTO),
        'width'  => 50,
        'height' => 50
    ],
    'auto-7' => fn() => [
        'file'   => verticalImage(),
        'style'  => ImageStyle::make('fit', 120, 100, LaruploadMediaStyle::AUTO),
        'width'  => 76,
        'height' => 100
    ],
]);

it('can resize webp', function() {
    resize(webp(), LaruploadImageLibrary::GD);
});

it('can resize png', function() {
    resize(png(), LaruploadImageLibrary::GD);
});

it('can resize gif', function() {
    resize(gif(), LaruploadImageLibrary::GD, 79, 100);
});

it('can resize svg', function() {
    resize(svg(), LaruploadImageLibrary::IMAGICK, 99, 100);
});

it('will upload resized images to remote disks', function() {
    $disk = 's3';
    $saveTo = 'image.jpg';
    Storage::fake($disk);
    $path = Storage::disk($disk)->path($saveTo);

    $image = new Image(jpg(), $disk, LaruploadImageLibrary::GD, 10);
    $image->resize(
        saveTo: $saveTo,
        style: ImageStyle::make('fit', 100, 100, LaruploadMediaStyle::FIT),
    );

    $files = Storage::disk($disk)->allFiles();


    expect($files)
        ->toBeArray()
        ->toHaveCount(1)
        ->toMatchArray([
            ltrim($path, '/')
        ]);

});

it('can get dominant color of jpg', function() {
    $expected = LaruploadTestConsts::IMAGE_DETAILS['jpg']['color'];

    dominant(jpg(), LaruploadImageLibrary::GD, $expected);
});

it('can get dominant color of png', function() {
    $expected = LaruploadTestConsts::IMAGE_DETAILS['png']['color'];

    dominant(png(), LaruploadImageLibrary::GD, $expected);
});

it('can get dominant color of webp', function() {
    $expected = LaruploadTestConsts::IMAGE_DETAILS['webp']['color'];

    dominant(webp(), LaruploadImageLibrary::GD, $expected);
});

it('can get dominant color of gif', function() {
    $expected = LaruploadTestConsts::IMAGE_DETAILS['gif']['color'];

    dominant(gif(), LaruploadImageLibrary::GD, $expected);
});

it('can get dominant color of svg', function() {
    $image = new Image(svg(), 'local', LaruploadImageLibrary::IMAGICK, 10);
    $color = $image->getDominantColor();

    expect($color)->toMatch(LaruploadTestConsts::HEX_REGEX);
});

it('can get dominant color of given file', function() {
    $color = $this->image->getDominantColor(png());
    $expected = LaruploadTestConsts::IMAGE_DETAILS['png']['color'];

    expect($color)->toBe($expected);
});

it('can get dominant color of given file path', function() {
    $path = png()->getRealPath();
    $color = $this->image->getDominantColor($path);
    $expected = LaruploadTestConsts::IMAGE_DETAILS['png']['color'];

    expect($color)->toBe($expected);
});

it('cant guess dominant color, if quality is not valid', function() {
    $image = new Image(jpg(), 'local', LaruploadImageLibrary::GD, -2);
    $color = $image->getDominantColor();

    expect($color)->toBeNull();
});
