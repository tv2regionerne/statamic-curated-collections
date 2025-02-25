<?php

namespace Tv2regionerne\StatamicCuratedCollection;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Permission;
use Statamic\Http\View\Composers\FieldComposer;
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
        Fieldtypes\CuratedCollectionPopup::class,
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
        $this->bootBroadcasting();

        View::composer(['statamic-curated-collections::curated-collections.blueprints.edit'], FieldComposer::class);
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
        Permission::group('curated-collections', 'All Curated Collections', function () {
            Permission::register('manage curated-collections', function ($permission) {
                $permission
                    ->label('Administrate Curated Collections (all permissions)')
                    ->description('Grants access to administrate Curated Collections settings and blueprints');
            });

            Permission::register('create curated-collections', function ($permission) {
                $permission
                    ->label('Create Curated Collections');
            });

            Permission::register('view curated-collections', function ($permission) {
                $permission
                    ->label('View Curated Collections');
            });

            Permission::register('edit curated-collections', function ($permission) {
                $permission
                    ->label('Edit Curated Collection');
            });

            Permission::register('delete curated-collections', function ($permission) {
                $permission
                    ->label('Delete Curated Collection');
            });
        });

        Permission::group('curated-collections-individual', 'Individual Curated Collections', function () {
            // rescue to prevent issue when migrations has not been run
            rescue(function () {
                CuratedCollection::all()->each(function ($collection) {
                    Permission::register("edit curated-collection {$collection->handle}", function ($permission) use (&$collection) {
                        $permission
                            ->label("Edit {$collection->title}");
                    });

                    Permission::register("delete curated-collection {$collection->handle}", function ($permission) use (&$collection) {
                        $permission
                            ->label("Delete {$collection->title}");
                    });

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
                                        Route::post('/', [CuratedCollectionEntriesController::class, 'store']);
                                        Route::delete('/', [CuratedCollectionEntriesController::class, 'destroyAll']);
                                        Route::post('reorder', [CuratedCollectionEntriesController::class, 'reorder']);
                                        Route::prefix('/{entry}')
                                            ->group(function () {
                                                Route::patch('/', [CuratedCollectionEntriesController::class, 'update']);
                                                Route::delete('/', [CuratedCollectionEntriesController::class, 'destroy']);
                                            });
                                    });
                            });
                    });
            });
        }

        return $this;
    }

    private function bootBroadcasting()
    {
        Broadcast::channel('curated-collections-private.{handle}', function ($user, string $id) {
            $user = \Statamic\Facades\User::fromUser($user);

            return $user->isSuper() || $user->can('access cp');
        }, ['guards' => ['web']]);

        return $this;
    }
}
