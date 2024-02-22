<?php

uses(\Tv2regionerne\StatamicCuratedCollection\Tests\TestCase::class);

use Illuminate\Support\Facades\Event;
use Statamic\Facades;
use Tv2regionerne\StatamicCuratedCollection\Events\CuratedCollectionTagEvent;
use Tv2regionerne\StatamicCuratedCollection\Models\CuratedCollection;
use Tv2regionerne\StatamicCuratedCollection\Models\CuratedCollectionEntry;

describe('curated collection tag tests', function () {

    function tag($tag, $data = [])
    {
        return (string) Facades\Parse::template($tag, $data);
    }

    function setupDummyCollectionAndEntries()
    {
        $collection = Facades\Collection::make()
            ->handle('articles')
            ->save();

        $entry1 = Facades\Entry::make()
            ->collection('articles')
            ->data([
                'title' => 'Entry 1',
                'sort_field' => 99,
            ])
            ->save();

        $entry3 = Facades\Entry::make()
            ->collection('articles')
            ->data([
                'title' => 'Entry 2',
                'sort_field' => 66,
            ])
            ->save();

        $entry3 = Facades\Entry::make()
            ->collection('articles')
            ->data([
                'title' => 'Entry 3',
                'sort_field' => 33,
            ])
            ->save();
    }

    it('doesnt output anything when the curated collection doesnt exit', function () {
        $result = tag('{{ curated_collection:test }}{{ /curated_collection:test }}');

        $this->assertSame('', $result);
    });

    it('falls back to a collection when no entries exist and fallback is true', function () {
        setupDummyCollectionAndEntries();

        $curated = CuratedCollection::factory()->make();
        $curated->handle = 'test';
        $curated->fallback_collection = 'articles';
        $curated->save();

        $result = tag('{{ curated_collection:test fallback="true" }}{{ title }}|{{ /curated_collection:test }}');

        $this->assertSame('Entry 1|Entry 2|Entry 3|', $result);

        $result = tag('{{ curated_collection:test fallback="true" }}{{ curated_collection_source }}|{{ /curated_collection:test }}');

        $this->assertSame('fallback|fallback|fallback|', $result);
    });

    it('doesn\'t fall back to a collection when no entries exist and fallback is false', function () {
        setupDummyCollectionAndEntries();

        $curated = CuratedCollection::factory()->make();
        $curated->handle = 'test';
        $curated->fallback_collection = 'articles';
        $curated->save();

        $result = tag('{{ curated_collection:test fallback="false" }}{{ title }}|{{ /curated_collection:test }}');

        $this->assertSame('|', $result);
    });

    it('sorts fall back entries by the specified sort direction', function () {
        setupDummyCollectionAndEntries();

        $curated = CuratedCollection::factory()->make();
        $curated->handle = 'test';
        $curated->fallback_collection = 'articles';
        $curated->fallback_sort_direction = 'desc';
        $curated->save();

        $result = tag('{{ curated_collection:test fallback="true" }}{{ title }}|{{ /curated_collection:test }}');

        $this->assertSame('Entry 3|Entry 2|Entry 1|', $result);
    });

    it('sorts fall back entries by the specified sort field', function () {
        setupDummyCollectionAndEntries();

        $curated = CuratedCollection::factory()->make();
        $curated->handle = 'test';
        $curated->fallback_collection = 'articles';
        $curated->fallback_sort_field = 'sort_field';
        $curated->save();

        $result = tag('{{ curated_collection:test fallback="true" }}{{ title }}|{{ /curated_collection:test }}');

        $this->assertSame('Entry 3|Entry 2|Entry 1|', $result);
    });

    it('limits fall back entries when all entries are fall back entries', function () {
        setupDummyCollectionAndEntries();

        $curated = CuratedCollection::factory()->make();
        $curated->handle = 'test';
        $curated->fallback_collection = 'articles';
        $curated->save();

        $result = tag('{{ curated_collection:test fallback="true" limit="1" }}{{ title }}{{ /curated_collection:test }}');

        $this->assertSame('Entry 1', $result);
    });

    it('adds to the as param when specified', function () {
        setupDummyCollectionAndEntries();

        $curated = CuratedCollection::factory()->make();
        $curated->handle = 'test';
        $curated->fallback_collection = 'articles';
        $curated->save();

        $result = tag('{{ curated_collection:test fallback="true" limit="1" as="test" }}{{ test }}{{ title }}{{ /test }}{{ /curated_collection:test }}');

        $this->assertSame('Entry 1', $result);
    });

    it('outputs curated collection entries', function () {
        setupDummyCollectionAndEntries();

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

        $result = tag('{{ curated_collection:test fallback="false" }}{{ title }}|{{ /curated_collection:test }}');

        $this->assertSame('Entry 4|Entry 5|', $result);
    });

    it('outputs curated collection entries and fall back entries', function () {
        setupDummyCollectionAndEntries();

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

        $result = tag('{{ curated_collection:test fallback="true" limit="3" }}{{ title }}|{{ /curated_collection:test }}');

        $this->assertSame('Entry 4|Entry 5|Entry 1|', $result);

        $result = tag('{{ curated_collection:test fallback="true" limit="3" }}{{ curated_collection_source }}|{{ /curated_collection:test }}');

        $this->assertSame('list|list|fallback|', $result);
    });

    it('fires a CuratedCollectionTagEvent', function () {
        setupDummyCollectionAndEntries();

        Event::fake();

        $curated = CuratedCollection::factory()->make();
        $curated->handle = 'test';
        $curated->fallback_collection = 'articles';
        $curated->save();

        $result = tag('{{ curated_collection:test fallback="true" limit="1" }}{{ title }}{{ /curated_collection:test }}');

        $this->assertSame('Entry 1', $result);

        Event::assertDispatched(CuratedCollectionTagEvent::class);
    });

    it ('ignores ids specified in dedeplicate and adds ids to deduplicate', function () {
        setupDummyCollectionAndEntries();

        $entry1 = Facades\Entry::query()->where('title', 'Entry 1')->first();
        app('deduplicate')->merge([$entry1->id()]);

        $curated = CuratedCollection::factory()->make();
        $curated->handle = 'test';
        $curated->fallback_collection = 'articles';
        $curated->save();

        $result = tag('{{ curated_collection:test fallback="true" limit="1" deduplicate="true" }}{{ title }}{{ /curated_collection:test }}');

        $this->assertSame('Entry 2', $result);

        $entry2 = Facades\Entry::query()->where('title', 'Entry 2')->first();

        $this->assertSame([$entry1->id(), $entry2->id()], app('deduplicate')->fetch());
    });
});
