<?php

use Mostafaznv\Larupload\Test\Support\Enums\LaruploadTestModels;

dataset('models', [
    'heavy' => fn() => LaruploadTestModels::HEAVY->instance(),
    'light' => fn() => LaruploadTestModels::LIGHT->instance(),
]);
