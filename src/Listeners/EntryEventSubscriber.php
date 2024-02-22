<?php

namespace Tv2regionerne\StatamicCuratedCollection\Listeners;

use Statamic\Events\EntryCreated;
use Statamic\Events\EntryDeleted;
use Statamic\Events\EntrySaved;
use Tv2regionerne\StatamicCuratedCollection\Events\CuratedCollectionUpdatedEvent;
use Tv2regionerne\StatamicCuratedCollection\Models\CuratedCollectionEntry;

class EntryEventSubscriber
{
    public function handleEntryCreated(EntryCreated $event)
    {
        // Don't do anything
    }

    public function handleEntryDeleted(EntryDeleted $event)
    {
        $curatedCollectionEntries = CuratedCollectionEntry::where('entry_id', $event->entry->id())->get();

        $curatedCollectionsToReorder = [];

        // delete all curatedColletionEntries related to the deleted entry
        $curatedCollectionEntries->each(function ($curatedCollectionEntry) use (&$curatedCollectionsToReorder) {
            $curatedCollectionEntry->delete();
            $curatedCollectionsToReorder[$curatedCollectionEntry->curatedCollection->id] = $curatedCollectionEntry->curatedCollection;
        });

        // reorder the collection entries where entries have been deleted
        foreach ($curatedCollectionsToReorder as $curatedCollection) {
            $curatedCollection->reorderEntries();
            CuratedCollectionUpdatedEvent::dispatch($curatedCollection->handle);
        }

    }

    public function handleEntrySaved(EntrySaved $event)
    {
        $entry = $event->entry;

        // Publish all curated collection entries
        if ($entry->status() === 'published') {
            $curatedCollectionEntries = CuratedCollectionEntry::where('status', 'draft')
                ->where('entry_id', $event->entry->id())
                ->get();

            $curatedCollectionEntries->each(function ($entry) {
                $entry->publish();
            });

            return;
        }

        // Delete any published curated collection entries
        $curatedCollectionEntries = CuratedCollectionEntry::where('status', 'published')
            ->where('entry_id', $event->entry->id())
            ->get();

        $curatedCollectionsToReorder = [];

        $curatedCollectionEntries->each(function (CuratedCollectionEntry $curatedCollectionEntry) use (&$curatedCollectionsToReorder) {
            $curatedCollectionEntry->delete();
            $curatedCollectionsToReorder[$curatedCollectionEntry->curatedCollection->id] = $curatedCollectionEntry->curatedCollection;
        });

        // reorder the collection entries where entries have been deleted
        foreach ($curatedCollectionsToReorder as $curatedCollection) {
            $curatedCollection->reorderEntries();

            CuratedCollectionUpdatedEvent::dispatch($curatedCollection->handle);
        }
    }

    public function subscribe($events)
    {
        return [
            EntryDeleted::class => 'handleEntryDeleted',
            EntryCreated::class => 'handleEntryCreated',
            EntrySaved::class => 'handleEntrySaved',
        ];
    }
}
