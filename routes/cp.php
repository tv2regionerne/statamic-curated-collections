<?php

use Illuminate\Support\Facades\Route;
use Tv2regionerne\StatamicCuratedCollection\Http\Controllers\CP\ApiEntriesController;
use Tv2regionerne\StatamicCuratedCollection\Http\Controllers\CP\ApiEntryRelationController;
use Tv2regionerne\StatamicCuratedCollection\Http\Controllers\CP\ApiLookupController;
use Tv2regionerne\StatamicCuratedCollection\Http\Controllers\CP\CuratedCollectionBlueprintController;
use Tv2regionerne\StatamicCuratedCollection\Http\Controllers\CP\CuratedCollectionController;

Route::resource('/curated-collections', CuratedCollectionController::class);

Route::prefix('/curated-collections')
    ->group(function () {

        Route::prefix('/{curatedCollection}/blueprint')
            ->name('curated-collections.blueprint.')
            ->group(function () {
                Route::get('/', [CuratedCollectionBlueprintController::class, 'edit'])->name('edit');
                Route::patch('/', [CuratedCollectionBlueprintController::class, 'update'])->name('update');
            });

        Route::prefix('/api')
            ->name('curated-collections.api.')
            ->group(function () {

                Route::prefix('/collections/{curatedCollection:handle}/entries')
                    ->name('entries.')
                    ->group(function () {
                        Route::get('/', [ApiEntriesController::class, 'index'])->name('entries.index');
                        Route::post('/', [ApiEntriesController::class, 'store'])->name('entries.store');
                        Route::post('/reorder', [ApiEntriesController::class, 'reorder'])->name('entries.reorder');
                        Route::get('/create', [ApiEntriesController::class, 'create'])->name('entries.create');
                        Route::get('/{curatedCollectionEntry}', [ApiEntriesController::class, 'edit'])->name('entries.edit');
                        Route::patch('/{curatedCollectionEntry}', [ApiEntriesController::class, 'update'])->name('entries.update');
                        Route::delete('/{curatedCollectionEntry}', [ApiEntriesController::class, 'destroy'])->name('entries.destroy');
                    });

                Route::get('/lookup', [ApiLookupController::class, 'index'])->name('lookup');
                Route::get('/entry-relations/{id}', [ApiEntryRelationController::class, 'index'])->name('entry-relation');

            });
    });
