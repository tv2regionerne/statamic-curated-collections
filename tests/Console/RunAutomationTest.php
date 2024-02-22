<?php

use Illuminate\Support\Facades\Event;
use Statamic\Facades;
use Tv2regionerne\StatamicCuratedCollection\Events\CuratedCollectionUpdatedEvent;
use Tv2regionerne\StatamicCuratedCollection\Models\CuratedCollection;
use Tv2regionerne\StatamicCuratedCollection\Models\CuratedCollectionEntry;

describe('run automation tests', function () {

    it('publishes draft entries', function () {
        setupDummyCollectionAndEntries();

        Event::fake([CuratedCollectionUpdatedEvent::class]);

        $entry4 = tap(Facades\Entry::make()
            ->collection('articles')
            ->data([
                'title' => 'Entry 4',
                'sort_field' => 11,
            ]))
            ->save();

        $entry5 = tap(Facades\Entry::make()
            ->collection('articles')
            ->data([
                'title' => 'Entry 5',
                'sort_field' => 22,
            ]))
            ->save();

        $curated = CuratedCollection::factory()->make();
        $curated->handle = 'test';
        $curated->fallback_collection = 'articles';
        $curated->save();

        $curatedEntry4 = CuratedCollectionEntry::factory()->make();
        $curatedEntry4->entry_id = $entry4->id();
        $curatedEntry4->curated_collection_id = $curated->id;
        $curatedEntry4->save();

        $curatedEntry5 = CuratedCollectionEntry::factory()->make();
        $curatedEntry5->entry_id = $entry5->id();
        $curatedEntry5->curated_collection_id = $curated->id;
        $curatedEntry5->status = 'draft';
        $curatedEntry5->save();

        $this->assertCount(1, $curated->entries()->where('status', 'published')->get());

        $this->artisan('curated-collections:automation');

        $this->assertCount(2, $curated->entries()->where('status', 'published')->get());
    });

    it('expires automated entries', function () {
        setupDummyCollectionAndEntries();

        Event::fake([CuratedCollectionUpdatedEvent::class]);

        $entry4 = tap(Facades\Entry::make()
            ->collection('articles')
            ->data([
                'title' => 'Entry 4',
                'sort_field' => 11,
            ]))
            ->save();

        $entry5 = tap(Facades\Entry::make()
            ->collection('articles')
            ->data([
                'title' => 'Entry 5',
                'sort_field' => 22,
            ]))
            ->save();

        $curated = CuratedCollection::factory()->make();
        $curated->handle = 'test';
        $curated->fallback_collection = 'articles';
        $curated->automation = true;
        $curated->save();

        $curatedEntry4 = CuratedCollectionEntry::factory()->make();
        $curatedEntry4->entry_id = $entry4->id();
        $curatedEntry4->curated_collection_id = $curated->id;
        $curatedEntry4->save();

        $curatedEntry5 = CuratedCollectionEntry::factory()->make();
        $curatedEntry5->entry_id = $entry5->id();
        $curatedEntry5->curated_collection_id = $curated->id;
        $curatedEntry5->unpublish_at = now()->subMinutes(5);
        $curatedEntry5->save();

        $this->assertCount(2, $curated->entries()->get());

        $this->artisan('curated-collections:automation');

        $this->assertCount(1, $curated->entries()->get());
    });

});
