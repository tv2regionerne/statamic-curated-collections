<?php

use Illuminate\Support\Facades\Event;
use Statamic\Facades;
use Tv2regionerne\StatamicCuratedCollection\Events\CuratedCollectionUpdatedEvent;
use Tv2regionerne\StatamicCuratedCollection\Models\CuratedCollection;
use Tv2regionerne\StatamicCuratedCollection\Models\CuratedCollectionEntry;

describe('entry event listener tests', function () {

    it('removes an entry through an entry deleted event', function () {
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
        $curatedEntry5->save();

        $this->assertCount(2, $curated->entries()->get());

        $entry5->delete();

        $this->assertCount(1, $curated->entries()->get());

        Event::assertDispatched(CuratedCollectionUpdatedEvent::class);
    });

    it('updates an draft entry through an entry saving event', function () {
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

        $entry5->save();

        $this->assertCount(2, $curated->entries()->where('status', 'published')->get());

        Event::assertNotDispatched(CuratedCollectionUpdatedEvent::class);
    });

    it('updates an published entry through an entry saving event', function () {
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
        $curatedEntry5->save();

        $this->assertCount(2, $curated->entries()->where('status', 'published')->get());

        $entry5->published(false);
        $entry5->save();

        $this->assertCount(1, $curated->entries()->where('status', 'published')->get());

        Event::assertDispatched(CuratedCollectionUpdatedEvent::class);
    });
});
