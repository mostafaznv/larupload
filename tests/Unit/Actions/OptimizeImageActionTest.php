<?php

use Mostafaznv\Larupload\Exceptions\InvalidImageOptimizerException;
use Illuminate\Http\UploadedFile;
use Mostafaznv\Larupload\Actions\OptimizeImageAction;


it('optimizes image successfully', function (UploadedFile $file) {
    # prepare
    $file = jpg();
    $realPath = $file->getRealPath();
    $name = $file->getClientOriginalName();
    $size = $file->getSize();


    # action
    $optimized = OptimizeImageAction::make($file)->process();


    # test
    expect($optimized->getRealPath())
        ->toBe($realPath)
        ->and($optimized->getClientOriginalName())
        ->toBe($name)
        ->and($optimized->getSize())
        ->toBeLessThan($size);

})->with([
    'jpg'    => fn() => jpg(),
    'jpg-fa' => fn() => jpg(true),
    'png'    => fn() => png(),
    'webp'   => fn() => webp(),
    'svg'    => fn() => svg(),
    'gif'    => fn() => gif(),
]);

it('throws exception for invalid optimizer classes', function () {
    config()->set('larupload.optimize-image.optimizers', [
        'InvalidOptimizerClass' => [
            '-m85',
            '--force',
        ]
    ]);

    OptimizeImageAction::make(jpg())->process();

})->throws(InvalidImageOptimizerException::class, "Configured optimizer [InvalidOptimizerClass] does not implement [Spatie\ImageOptimizer\Optimizer].");
