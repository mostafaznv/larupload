<?php

namespace Mostafaznv\Larupload\Test;

use Illuminate\Foundation\Application;
use Mostafaznv\Larupload\LaruploadServiceProvider;
use Mostafaznv\Larupload\Test\Support\Enums\LaruploadTestModels;
use Mostafaznv\Larupload\Test\Support\LaruploadTestTablesMigration;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);
    }

    protected function getPackageProviders($app): array
    {
        return [
            LaruploadServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
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
        $app['config']->set('filesystems.disks.local.root', public_path('uploads'));
        $app['config']->set('filesystems.disks.local.url', $app['config']->get('app.url') . '/uploads');
        $app['config']->set('filesystems.disks.local.visibility', 'public');
    }

    protected function setUpDatabase(Application $app)
    {
        $migration = new LaruploadTestTablesMigration($app);
        $migration->migrate();
    }
}
