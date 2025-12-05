<?php

use Mostafaznv\Larupload\Enums\LaruploadMode;
use Mostafaznv\Larupload\Test\Support\Enums\LaruploadTestModels;
use Mostafaznv\Larupload\Test\Support\TestAttachmentBuilder;

dataset('models', [
    'heavy' => fn() => LaruploadTestModels::HEAVY->instance(),
    'light' => fn() => LaruploadTestModels::LIGHT->instance(),
]);

dataset('models with dominant color', [
    'heavy' => function () {
        $model = LaruploadTestModels::HEAVY->instance();
        $model->setAttachments(
            TestAttachmentBuilder::make(LaruploadMode::HEAVY)
                ->calculateDominantColor()
                ->toArray()
        );

        return $model;
    },
    'light' => function () {
        $model = LaruploadTestModels::LIGHT->instance();
        $model->setAttachments(
            TestAttachmentBuilder::make(LaruploadMode::LIGHT)
                ->calculateDominantColor()
                ->toArray()
        );

        return $model;
    },
]);
