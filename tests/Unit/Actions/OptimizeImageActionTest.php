<?php

use Mostafaznv\Larupload\Exceptions\InvalidImageOptimizerException;
use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Storage\Attachment;
use Mostafaznv\Larupload\Test\Support\Enums\LaruploadTestModels;
use Illuminate\Http\UploadedFile;
use Mostafaznv\Larupload\Actions\OptimizeImageAction;


beforeEach(function () {
    $this->disk = 'public';

    Queue::fake();
    Storage::fake('s3');
    Storage::fake($this->disk);

    $this->attachment = Attachment::make('main_file');
    $this->attachment->disk = $this->disk;
    $this->attachment->localDisk = $this->disk;
    $this->attachment->folder = 'test-folder';
    $this->attachment->nameKebab = 'main-file';
    $this->attachment->id = 'test-id';

    $this->path = larupload_relative_path($this->attachment, $this->attachment->id);
    $this->original = $this->path . '/' . Larupload::ORIGINAL_FOLDER;
    $this->cover = $this->path . '/' . Larupload::COVER_FOLDER;

    $this->model = LaruploadTestModels::HEAVY->instance();
    $this->model->id = 52;
});


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
