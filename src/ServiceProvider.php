<?php

namespace Tv2regionerne\StatamicCuratedCollection;

use Statamic\Facades\Permission;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Facades\CP\Nav;
use Tv2regionerne\StatamicCuratedCollection\Commands\RunAutomation;
use Tv2regionerne\StatamicCuratedCollection\Listeners\EntryEventSubscriber;
use Tv2regionerne\StatamicCuratedCollection\Models\CuratedCollection;
use Tv2regionerne\StatamicCuratedCollection\Models\CuratedCollectionEntry;
use Tv2regionerne\StatamicCuratedCollection\Policies\CuratedCollectionEntryPolicy;
use Tv2regionerne\StatamicCuratedCollection\Policies\CuratedCollectionPolicy;
use Tv2regionerne\StatamicCuratedCollection\Filters\ActiveStatus;

class ServiceProvider extends AddonServiceProvider
{

    protected $routes = [
        'cp' => __DIR__ . '/../routes/cp.php',
    ];
    protected $tags = [
        Tags\StatamicCuratedCollection::class,
    ];
    protected $fieldtypes = [
        Fieldtypes\CuratedCollection::class,
    ];

    protected $commands = [
        RunAutomation::class,
    ];

    protected $vite = [
        'resources/js/curated-collections-addon.js'
    ];

    protected $subscribe = [
        EntryEventSubscriber::class,
    ];

    protected $scopes = [
        ActiveStatus::class,
    ];

    protected $policies = [
        CuratedCollection::class => CuratedCollectionPolicy::class,
        CuratedCollectionEntry::class => CuratedCollectionEntryPolicy::class,
    ];

    public function boot()
    {
        parent::boot();
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
    public function bootAddon()
    {
        $this->bootPermissions();

        Nav::extend(function ($nav) {

            $children = [];
            foreach (CuratedCollection::query()->orderBy('title')->get() as $list) {
                $children[] = $nav->item($list->title)->route('curated-collections.show', $list->handle);
            }

            $nav->content(__('statamic-curated-collections::messages.title_plural'))
                ->section('Content')
                ->route('curated-collections.index')
                ->icon('list')
                ->children($children);
        });
    }

    protected function bootPermissions(): void
    {

        Permission::group('curated-collections', 'Curated Collections', function () {
            Permission::register("access curated-collections", function ($permission) {
                return $permission
                    ->label('Access Bazo Import')
                    ->description("Grants access to Bazo Import");
            });
            Permission::register("edit bazo import settings", function ($permission) {
                return $permission
                    ->label('Edit Bazo Import settings')
                    ->description("Grants access to Bazo Import settings");
            });
        });
    }
}
