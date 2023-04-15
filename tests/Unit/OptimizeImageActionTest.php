<?php

use Mostafaznv\Larupload\Actions\OptimizeImageAction;

it('can optimize jpg', function() {
    $file = jpg();
    $size = $file->getSize();
    $optimized = OptimizeImageAction::make($file)->process();

    expect($size)->toBeGreaterThan($optimized->getSize());
});

it('can optimize png', function() {
    $file = png();
    $size = $file->getSize();
    $optimized = OptimizeImageAction::make($file)->process();

    expect($size)->toBeGreaterThan($optimized->getSize());
});

it('can optimize svg', function() {
    $file = svg();
    $size = $file->getSize();
    $optimized = OptimizeImageAction::make($file)->process();

    expect($size)->toBeGreaterThan($optimized->getSize());
});

it('can optimize gif', function() {
    $file = gif();
    $size = $file->getSize();
    $optimized = OptimizeImageAction::make($file)->process();

    expect($size)->toBeGreaterThan($optimized->getSize());
});

it('can optimize webp', function() {
    $file = webp();
    $size = $file->getSize();
    $optimized = OptimizeImageAction::make($file)->process();

    expect($size)->not()->toBe($optimized->getSize());
});

it('will throw an exception if optimizer is not an instance of optimizer class', function() {
    config()->set('larupload.optimize-image.optimizers', [
        OptimizeImageAction::class => [
            '-m85',
            '--force',
        ]
    ]);

    $file = jpg();
    OptimizeImageAction::make($file)->process();
})->throws(Exception::class, 'Configured optimizer `Mostafaznv\Larupload\Actions\OptimizeImageAction` does not implement `Spatie\ImageOptimizer\Optimizer`.');

