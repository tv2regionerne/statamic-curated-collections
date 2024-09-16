<?php

namespace Tv2regionerne\StatamicCuratedCollection\Tests;

use Illuminate\Encryption\Encrypter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Statamic\Testing\AddonTestCase;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tv2regionerne\StatamicCuratedCollection\ServiceProvider;

abstract class TestCase extends AddonTestCase
{
    use PreventsSavingStacheItemsToDisk, RefreshDatabase;

    protected $fakeStacheDirectory = __DIR__.'/__fixtures__/dev-null';

    protected string $addonServiceProvider = ServiceProvider::class;

    protected $shouldFakeVersion = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->runLaravelMigrations();
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']->set('app.key', 'base64:'.base64_encode(
            Encrypter::generateKey($app['config']['app.cipher'])
        ));

        $app['config']->set('statamic.stache.watcher', false);
        $app['config']->set('statamic.stache.stores.collections.directory', __DIR__.'/__fixtures__/content/collections');
        $app['config']->set('statamic.stache.stores.entries.directory', __DIR__.'/__fixtures__/content/collections');

        $app['config']->set('curated-collections', require(__DIR__.'/../config/curated-collections.php'));

        $app['config']->set('app.debug', true);

        $app->singleton('deduplicate', function () {
            return new Deduplicate();
        });
    }
}
