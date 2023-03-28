<?php

use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;
use Mostafaznv\Larupload\LaruploadServiceProvider;


it('is a service provider', function() {
    $class = LaruploadServiceProvider::class;
    $reflection = new ReflectionClass($class);

    $provider = new ReflectionClass(ServiceProvider::class);

    expect($reflection->isSubclassOf($provider))
        ->toBeTrue("Expected class '{$class}' to be a service provider.");
});

it('has provides method', function() {
    $class = LaruploadServiceProvider::class;
    $reflection = new ReflectionClass($class);

    $method = $reflection->getMethod('provides');
    $method->setAccessible(true);

    expect($method->invoke(new $class(new Container())))
        ->toBeArray("Expected class '{$class}' to provide a valid list of services.");
});
