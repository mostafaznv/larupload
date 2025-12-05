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
    $res = OptimizeImageAction::make($file)->process();


    # test
    expect($res->getRealPath())
        ->toBe($realPath)
        ->and($res->getClientOriginalName())
        ->toBe($name)
        ->and($res->getSize())
        ->toBeLessThan($size);

})->with([
    fn() => jpg(),
    fn() => jpg(true),
    fn() => png(),
    fn() => webp(),
    fn() => svg(),
    fn() => gif(),
]);

it('throws exception for invalid optimizer classes', function () {
    config()->set('larupload.optimize-image', [
        'timeout'    => 10,
        'optimizers' => [
            'InvalidOptimizerClass' => []
        ]
    ]);

    OptimizeImageAction::make(jpg())->process();

})->throws(InvalidImageOptimizerException::class, "Configured optimizer [InvalidOptimizerClass] does not implement [Spatie\ImageOptimizer\Optimizer].");
