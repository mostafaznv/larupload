<?php

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Bus;
use Mostafaznv\Larupload\Events\LaruploadProcessFinished;
use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Test\Support\Enums\LaruploadTestModels;


beforeEach(function () {
    Bus::fake();
    Queue::fake();
    Event::fake(LaruploadProcessFinished::class);
});


it('dispatches `LaruploadProcessFinished` after saving the model', function (LaruploadTestModels $model) {
    Event::assertNotDispatched(LaruploadProcessFinished::class);

    save($model->instance(), jpg());

    Event::assertDispatched(LaruploadProcessFinished::class);

})->with([
    'HEAVY' => fn() => LaruploadTestModels::HEAVY,
    'LIGHT' => fn() => LaruploadTestModels::LIGHT,
    'QUEUE' => fn() => LaruploadTestModels::QUEUE,
]);

it('dispatches `LaruploadProcessFinished` after saving in standalone mode', function () {
    Event::assertNotDispatched(LaruploadProcessFinished::class);

    Larupload::init('uploader')->upload(mp3());

    Event::assertDispatched(LaruploadProcessFinished::class);
});

