<?php

namespace Tv2regionerne\StatamicCuratedCollection;

use Illuminate\Support\Facades\Route;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Permission;
use Statamic\Providers\AddonServiceProvider;
use Tv2regionerne\StatamicCuratedCollection\Commands\RunAutomation;
use Tv2regionerne\StatamicCuratedCollection\Filters\ActiveStatus;
use Tv2regionerne\StatamicCuratedCollection\Http\Controllers\Api\CuratedCollectionController;
use Tv2regionerne\StatamicCuratedCollection\Http\Controllers\Api\CuratedCollectionEntriesController;
use Tv2regionerne\StatamicCuratedCollection\Listeners\EntryEventSubscriber;
use Tv2regionerne\StatamicCuratedCollection\Models\CuratedCollection;
use Tv2regionerne\StatamicCuratedCollection\Models\CuratedCollectionEntry;
use Tv2regionerne\StatamicCuratedCollection\Policies\CuratedCollectionEntryPolicy;
use Tv2regionerne\StatamicCuratedCollection\Policies\CuratedCollectionPolicy;
use Tv2regionerne\StatamicPrivateApi\Facades\PrivateApi;

class ServiceProvider extends AddonServiceProvider
{
    protected $routes = [
        'cp' => __DIR__.'/../routes/cp.php',
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
        'resources/js/curated-collections-addon.js',
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

        $this->bootApi();
    }

    public function bootAddon()
    {
        $this->bootPermissions();

        Nav::extend(function ($nav) {

            $children = [];
            rescue(function () use (&$nav, &$children) {
                foreach (CuratedCollection::query()->orderBy('title')->get() as $list) {
                    $children[] = $nav->item($list->title)
                        ->can("view curated-collection {$list->handle} entries", $list)
                        ->route('curated-collections.show', $list->handle);
                }
            });

            $nav->content(__('statamic-curated-collections::messages.title_plural'))
                ->section('Content')
                ->route('curated-collections.index')
                ->icon('list')
                ->children($children);
        });
    }

    protected function bootPermissions(): self
    {
        Permission::group('curated-collections', 'Curated Collections', function () {
            Permission::register('manage curated-collections', function ($permission) {
                $permission
                    ->label('Administrate Curated Collections')
                    ->description('Grants access to administrate Curated Collections settings and blueprints');
            });

            // rescue to prevent issue when migrations has not been run
            rescue(function () {
                CuratedCollection::all()->each(function ($collection) {
                    Permission::register("view curated-collection {$collection->handle} entries", function ($permission) use (&$collection) {
                        $permission
                            ->label("View {$collection->title} entries")
                            ->children([
                                Permission::make("edit curated-collection {$collection->handle} entries")
                                    ->label("Edit {$collection->title} entries")
                                    ->children([
                                        Permission::make("create curated-collection {$collection->handle} entries")
                                            ->label("Create {$collection->title} entries"),
                                        Permission::make("delete curated-collection {$collection->handle} entries")
                                            ->label("Delete {$collection->title} entries"),
                                    ]),
                            ]);
                    });
                });
            });

        });

        return $this;
    }

    private function bootApi(): self
    {
        if (class_exists(PrivateApi::class)) {
            PrivateApi::addRoute(function () {
                Route::prefix('/statamic-curated-collections')
                    ->group(function () {
                        Route::get('/', [CuratedCollectionController::class, 'index']);
                        Route::post('/', [CuratedCollectionController::class, 'store']);

                        Route::prefix('/{id}')
                            ->group(function () {
                                Route::get('/', [CuratedCollectionController::class, 'show']);
                                Route::patch('/', [CuratedCollectionController::class, 'update']);
                                Route::delete('/', [CuratedCollectionController::class, 'destroy']);

                                Route::prefix('/entries')
                                    ->group(function () {
                                        Route::get('/', [CuratedCollectionEntriesController::class, 'index']);
                                        Route::post('reorder', [CuratedCollectionEntriesController::class, 'reorder']);
                                    });

                            });
                    });
            });
        }

        return $this;
    }
}
