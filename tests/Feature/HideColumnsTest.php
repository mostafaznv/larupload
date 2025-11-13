<?php

use Mostafaznv\Larupload\Test\Support\Enums\LaruploadTestModels;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadHeavyTestModel;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadLightTestModel;


it('will show larupload columns in toArray response', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    config()->set('larupload.hide-table-columns', false);

    $model = $model::class;
    $model = save(new $model, jpg());
    $array = $model->toArray();

    expect($array)->toHaveKey('main_file_file_name');

})->with('models');

it('will hide larupload columns from toArray response', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    config()->set('larupload.hide-table-columns', true);

    $model = $model::class;
    $model = save(new $model, jpg());
    $array = $model->toArray();

    foreach ($this->metaKeys as $meta) {
        expect(isset($array["main_file_file_$meta"]))
            ->toBeFalse();
    }

})->with('models');

it('will hide file_original_name columns from toArray response', function() {
    config()->set('larupload.store-original-file-name', true);
    config()->set('larupload.hide-table-columns', false);

    $model = LaruploadTestModels::HEAVY->instance();
    $model = save($model, jpg());
    $array = $model->toArray();

    expect($array)->toHaveKey('main_file_file_original_name');

    config()->set('larupload.hide-table-columns', true);

    $model = LaruploadTestModels::HEAVY->instance();
    $model = save($model, jpg());
    $array = $model->toArray();

    expect($array)->not->toHaveKey('main_file_file_original_name');

});
