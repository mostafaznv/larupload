<?php

use Mostafaznv\Larupload\Storage\Proxy\AttachmentProxy;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadHeavyTestModel;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadLightTestModel;


it('will return attachment on retrieving attachment property', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save($model, jpg());

    expect($model->main_file)
        ->toBeInstanceOf(AttachmentProxy::class)
        //->toHaveMethods(get_class_methods(AttachmentProxy::class))
        ->and($model->not_exists_property)
        ->toBeNull()
        ->and($model->id)
        ->toBeInt();

    $methods = get_class_methods($model->main_file);
    expect($methods)
        ->toBeArray()
        ->toBe([
            '__construct',
            'attach',
            'detach',
            'cover',
            'meta',
            'urls',
            'url',
            'download',
            'handleFFMpegQueue',
        ]);

})->with('models');
