<?php

use Mostafaznv\Larupload\Helpers\Slug;

it('will get default lang from config, if it is not set in make function', function() {
    config()->set('app.locale', 'fa');
    $result = Slug::make()->generate('متن فارسی');

    expect($result)->toBe('متن-فارسی');

});

it('will generate slug', function(string $lang, string $text, string $slug) {
    $result = Slug::make($lang)->generate($text);

    expect($result)->toBe($slug);

})->with([
    ['en', 'ă test text ۱', 'a-test-text-1'],
    ['en', 'test@text', 'test-at-text'],
    ['de', 'Einen schönen Tag ö', 'einen-schoenen-tag-oe'],
    ['fa', 'متن فارسی ۱', 'متن-فارسی-۱'],
    ['fa', 'متن فارسی با نیم‌فاصله', 'متن-فارسی-با-نیمفاصله'],
]);
