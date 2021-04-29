<?php

namespace Mostafaznv\Larupload\Test;

use Mostafaznv\Larupload\LaruploadServiceProvider;
use Mostafaznv\Larupload\Test\Migrations\LaruploadSetupTables;
use Orchestra\Testbench\TestCase;

class LaruploadTestCase extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [LaruploadServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('cache.default', 'array');
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['config']->set('filesystems.default', 'local');
        $app['config']->set('filesystems.disks.local.driver', 'local');
        $app['config']->set('filesystems.disks.local.root', public_path());
        $app['config']->set('filesystems.disks.local.url', env('APP_URL'));
    }

    public function migrate()
    {
        $migrations = [
            LaruploadSetupTables::class,
        ];

        foreach ($migrations as $migration) {
            (new $migration)->up();
        }
    }
}
