<?php

use Illuminate\Support\Facades\Route;
use \Tv2regionerne\StatamicCuratedCollection\Http\Controllers\CP\CuratedCollectionController;
use \Tv2regionerne\StatamicCuratedCollection\Http\Controllers\CP\CuratedCollectionBlueprintController;
use \Tv2regionerne\StatamicCuratedCollection\Http\Controllers\CP\CuratedCollectionEntriesController;
use \Tv2regionerne\StatamicCuratedCollection\Http\Controllers\CP\ApiEntriesController;
use \Tv2regionerne\StatamicCuratedCollection\Http\Controllers\CP\ApiEntryRelationController;

Route::get('/curated-collections/api/collections/{curatedCollection:handle}/entries', [ApiEntriesController::class, 'index'])->name('curated-collections.api.entries.index');
Route::post('/curated-collections/api/collections/{curatedCollection:handle}/entries', [ApiEntriesController::class, 'store'])->name('curated-collections.api.entries.store');
Route::post('/curated-collections/api/collections/{curatedCollection:handle}/entries/reorder', [ApiEntriesController::class, 'reorder'])->name('curated-collections.api.entries.reorder');
Route::get('/curated-collections/api/collections/{curatedCollection:handle}/entries/create', [ApiEntriesController::class, 'create'])->name('curated-collections.api.entries.edit');
Route::get('/curated-collections/api/collections/{curatedCollection:handle}/entries/{curatedCollectionEntry}', [ApiEntriesController::class, 'edit'])->name('curated-collections.api.entries.edit');
Route::patch('/curated-collections/api/collections/{curatedCollection:handle}/entries/{curatedCollectionEntry}', [ApiEntriesController::class, 'update'])->name('curated-collections.api.entries.update');
Route::delete('/curated-collections/api/collections/{curatedCollection:handle}/entries/{curatedCollectionEntry}', [ApiEntriesController::class, 'destroy'])->name('curated-collections.api.entries.destroy');

Route::get('/curated-collections/api/entry-relations/{id}', [ApiEntryRelationController::class, 'index'])->name('curated-collections.api.entry-relation');

Route::resource('curated-collections', CuratedCollectionController::class);
Route::get('/curated-collections/{curatedCollection}/blueprint', [CuratedCollectionBlueprintController::class, 'edit'])->name('curated-collections.blueprint.edit');
Route::patch('/curated-collections/{curatedCollection}/blueprint', [CuratedCollectionBlueprintController::class, 'update'])->name('curated-collections.blueprint.update');
Route::resource('curated-collections/{curatedCollection:handle}/entries', CuratedCollectionEntriesController::class);
