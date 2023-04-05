<?php

use Hashids\Hashids;
use Mostafaznv\Larupload\Enums\LaruploadSecureIdsMethod;
use Illuminate\Support\Str;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadHeavyTestModel;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadLightTestModel;


it('will hide ids using ulid method', function (LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    config()->set('larupload.secure-ids', LaruploadSecureIdsMethod::ULID);

    $model = $model::class;
    $model = save(new $model, jpg());

    $id = $model->main_file->meta('id');

    expect(Str::isUlid($id))->toBeTrue()
        ->and($model->main_file->url())
        ->toContain($id)
        ->toBeExists()
        //
        ->and($model->main_file->url('cover'))
        ->toContain($id)
        ->toBeExists()
        //
        ->and($model->main_file->url('landscape'))
        ->toContain($id)
        ->toBeExists();

    $model = save(new $model, mp4());
    $id = $model->main_file->meta('id');

    expect(Str::isUlid($id))->toBeTrue()
        ->and($model->main_file->url())
        ->toContain($id)
        ->toBeExists()
        //
        ->and($model->main_file->url('cover'))
        ->toContain($id)
        ->toBeExists()
        //
        ->and($model->main_file->url('stream'))
        ->toContain($id)
        ->toBeExists()
        //
        ->and($model->main_file->url('landscape'))
        ->toContain($id)
        ->toBeExists();

})->with('models');

it('will hide ids using uuid method', function (LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    config()->set('larupload.secure-ids', LaruploadSecureIdsMethod::UUID);

    $model = $model::class;
    $model = save(new $model, jpg());

    $id = $model->main_file->meta('id');

    expect(Str::isUuid($id))->toBeTrue()
        ->and($model->main_file->url())
        ->toContain($id)
        ->toBeExists()
        //
        ->and($model->main_file->url('cover'))
        ->toContain($id)
        ->toBeExists()
        //
        ->and($model->main_file->url('landscape'))
        ->toContain($id)
        ->toBeExists();

    $model = save(new $model, mp4());
    $id = $model->main_file->meta('id');

    expect(Str::isUuid($id))->toBeTrue()
        ->and($model->main_file->url())
        ->toContain($id)
        ->toBeExists()
        //
        ->and($model->main_file->url('cover'))
        ->toContain($id)
        ->toBeExists()
        //
        ->and($model->main_file->url('stream'))
        ->toContain($id)
        ->toBeExists()
        //
        ->and($model->main_file->url('landscape'))
        ->toContain($id)
        ->toBeExists();

})->with('models');

it('will hide ids using hashid method', function (LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    config()->set('larupload.secure-ids', LaruploadSecureIdsMethod::HASHID);

    $model = $model::class;
    $model = save(new $model, jpg());

    $hashIds = new Hashids(config('app.key'), 20);

    $id = $model->main_file->meta('id');


    expect($hashIds->decode($id))->toBe([1])
        ->and($model->main_file->url())
        ->toContain($id)
        ->toBeExists()
        //
        ->and($model->main_file->url('cover'))
        ->toContain($id)
        ->toBeExists()
        //
        ->and($model->main_file->url('landscape'))
        ->toContain($id)
        ->toBeExists();

    $model = save(new $model, mp4());
    $id = $model->main_file->meta('id');

    expect($hashIds->decode($id))->toBe([2])
        ->and($model->main_file->url())
        ->toContain($id)
        ->toBeExists()
        //
        ->and($model->main_file->url('cover'))
        ->toContain($id)
        ->toBeExists()
        //
        ->and($model->main_file->url('stream'))
        ->toContain($id)
        ->toBeExists()
        //
        ->and($model->main_file->url('landscape'))
        ->toContain($id)
        ->toBeExists();

})->with('models');

it('wont hide hide id in upload path when secure-ids is disabled', function (LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save($model, jpg());
    $id = $model->main_file->meta('id');

    expect($id)->toBe('1')
        ->and($model->main_file->url())
        ->toContain($id)
        ->toBeExists()
        //
        ->and($model->main_file->url('cover'))
        ->toContain($id)
        ->toBeExists()
        //
        ->and($model->main_file->url('landscape'))
        ->toContain($id)
        ->toBeExists();

    $model = save($model, mp4());
    $id = $model->main_file->meta('id');

    expect($id)->toBe('1')
        ->and($model->main_file->url())
        ->toContain($id)
        ->toBeExists()
        //
        ->and($model->main_file->url('cover'))
        ->toContain($id)
        ->toBeExists()
        //
        ->and($model->main_file->url('stream'))
        ->toContain($id)
        ->toBeExists()
        //
        ->and($model->main_file->url('landscape'))
        ->toContain($id)
        ->toBeExists();

})->with('models');
