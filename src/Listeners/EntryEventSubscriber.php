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
        $curatedColletionEntries = CuratedCollectionEntry::where('entry_id', $event->entry->id())->get();

        $curatedCollectionsToReorder = [];

        // delete all curatedColletionEntries related to the deleted entry
        $curatedColletionEntries->each(function ($curatedColletionEntry) use (&$curatedCollectionsToReorder) {
            $curatedColletionEntry->delete();
            $curatedCollectionsToReorder[$curatedColletionEntry->curatedCollection->id] = $curatedColletionEntry->curatedCollection;
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
        if ($entry->status() === 'published') {
            // Publish all curated collection entries
            $curatedColletionEntries = CuratedCollectionEntry::where('status', 'draft')
                ->where('entry_id', $event->entry->id())
                ->get();

            $curatedColletionEntries->each(function ($entry) {
                $entry->publish();
            });

        } else {
            // Delete any published curated collection entries
            $curatedColletionEntries = CuratedCollectionEntry::where('status', 'published')
                ->where('entry_id', $event->entry->id())
                ->get();

            $curatedCollectionsToReorder = [];

            $curatedColletionEntries->each(function (CuratedCollectionEntry $curatedCollectionEntry) use (&$curatedCollectionsToReorder) {
                $curatedCollectionEntry->delete();
                $curatedCollectionsToReorder[$curatedCollectionEntry->curatedCollection->id] = $curatedCollectionEntry->curatedCollection;
            });

            // reorder the collection entries where entries have been deleted
            foreach ($curatedCollectionsToReorder as $curatedCollection) {
                $curatedCollection->reorderEntries();
                CuratedCollectionUpdatedEvent::dispatch($curatedCollection->handle);
            }
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
