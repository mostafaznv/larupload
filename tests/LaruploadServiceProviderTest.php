<?php

namespace Mostafaznv\Larupload\Test;

use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;
use ReflectionClass;
use Mostafaznv\Larupload\LaruploadServiceProvider;

class LaruploadServiceProviderTest extends LaruploadTestCase
{
    public function testItIsServiceProvider()
    {
        $class = $this->getServiceProviderClass();

        $reflection = new ReflectionClass($class);

        $provider = new ReflectionClass(ServiceProvider::class);

        $msg = "Expected class '{$class}' to be a service provider.";

        $this->assertTrue($reflection->isSubclassOf($provider), $msg);
    }

    public function testItHasProvidesMethod()
    {
        $class = $this->getServiceProviderClass();
        $reflection = new ReflectionClass($class);

        $method = $reflection->getMethod('provides');
        $method->setAccessible(true);

        $msg = "Expected class '{$class}' to provide a valid list of services.";

        $this->assertIsArray($method->invoke(new $class(new Container())), $msg);
    }

    /**
     * Get the service provider class
     */
    protected function getServiceProviderClass(): string
    {
        return LaruploadServiceProvider::class;
    }
}
