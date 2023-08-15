<?php

namespace Tv2regionerne\StatamicCuratedCollection\Http\Controllers\CP;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Statamic\Facades\Entry;
use Tv2regionerne\StatamicCuratedCollection\Http\Requests\CuratedCollectionEntryIndexRequest;
use Tv2regionerne\StatamicCuratedCollection\Http\Requests\CuratedCollectionEntryReorderRequest;
use Tv2regionerne\StatamicCuratedCollection\Http\Requests\CuratedCollectionEntryStoreRequest;
use Tv2regionerne\StatamicCuratedCollection\Http\Requests\CuratedCollectionEntryEditRequest;
use Tv2regionerne\StatamicCuratedCollection\Http\Requests\CuratedCollectionEntryUpdateRequest;
use Tv2regionerne\StatamicCuratedCollection\Http\Resources\CuratedCollectionEntryCollection;
use Tv2regionerne\StatamicCuratedCollection\Http\Resources\CuratedCollectionEntryResource;
use Tv2regionerne\StatamicCuratedCollection\Http\Resources\CuratedCollectionEntryEditResource;
use Tv2regionerne\StatamicCuratedCollection\Models\CuratedCollection;
use Tv2regionerne\StatamicCuratedCollection\Models\CuratedCollectionEntry;

class ApiEntriesController
{

    public function index(CuratedCollectionEntryIndexRequest $request, CuratedCollection $curatedCollection)
    {
        $query = CuratedCollectionEntry::query()
            ->where('curated_collection_id', $curatedCollection->id)
            ->ordered();

        // filter on status
        if ($request->has('status')) {
            if ($request->input('status') === 'draft') {
                $query->draft();
            } elseif ($request->input('status') === 'published') {
                $query->published();
            }
        }

        // Return all (No pagination)
        return (new CuratedCollectionEntryCollection($query->get()))
            ->blueprint($curatedCollection->addEntryBlueprint());
    }

    public function reorder(CuratedCollectionEntryReorderRequest $request, CuratedCollection $curatedCollection)
    {
        $ids = $request->input('ids');
        CuratedCollectionEntry::setNewOrder($ids);
    }

    public function store(CuratedCollectionEntryStoreRequest $request, CuratedCollection $curatedCollection)
    {
        /** @var \Statamic\Fields\Blueprint $blueprint */
        $blueprint = $curatedCollection->addEntryBlueprint();
        $fields = $blueprint->fields()->addValues($request->all());
        $data = $fields->validate();

        $entryId = $data['entry'][0];

        /** @var \Statamic\Entries\Entry $entry */
        $entry = Entry::find($entryId);

        /** @var CuratedCollectionEntry $curatedCollectionEntry */
        $curatedCollectionEntry = CuratedCollectionEntry::make();
        $curatedCollectionEntry->curatedCollection()->associate($curatedCollection);
        $curatedCollectionEntry->entry($entry);
        $curatedCollectionEntry->data(collect($data)->except(['curated_collection', 'entry', 'order', 'unpublish_at']));
        $curatedCollectionEntry->collection($entry->collection());

        if ($entry->status() === 'published') {
            // We got a published entry
            $curatedCollectionEntry->setHighestOrderNumber();

            // Unpublish at
            if (isset($data['unpublish_at'])) {
                $unpublishAt = $data['unpublish_at'];
            } elseif ($curatedCollection->update_expiration_on_publish) {
                $unpublishAt = now()->addHours($curatedCollection->expiration_time);
            }

            if ($unpublishAt) {
                $curatedCollectionEntry->unpublishAt($unpublishAt);
            }

            $curatedCollectionEntry->status('published');

        } else {
            // We got a draft entry

            // Set wanted publish order. Null to add to the bottom of the list.
            $curatedCollectionEntry->publish_order = $data['publish_order'] ?? null;


            // Override the automated expiration time
            $curatedCollectionEntry->expiration_time = $data['expiration_time'] ?? null;

            // set the unpublish time absolute
            if ($data['unpublish_at'] ?? null) {
                $curatedCollectionEntry->unpublishAt($data['unpublish_at']);
            }

            $curatedCollectionEntry->status('draft');
        }

        $curatedCollectionEntry->save();

        // set custom order
        if ($entry->published() && $data['order']) {
            $curatedCollectionEntry->setPosition($request->input('order'));
        }

        return new CuratedCollectionEntryResource($curatedCollectionEntry);
    }

    public function create(CuratedCollectionEntryEditRequest $request, CuratedCollection $curatedCollection)
    {
        $entryId = $request->input('entry');

        /** @var \Statamic\Entries\Entry $entry */
        $entry = Entry::find($entryId);

        $curatedCollectionEntry = CuratedCollectionEntry::make();
        $curatedCollectionEntry->curatedCollection()->associate($curatedCollection);
        $curatedCollectionEntry->entry($entry);
        $curatedCollectionEntry->collection($entry->collection()->handle());

        return (new CuratedCollectionEntryEditResource($curatedCollectionEntry))
            ->blueprint($curatedCollection->addEntryBlueprint());
    }

    public function edit(CuratedCollectionEntryEditRequest $request, CuratedCollection $curatedCollection, CuratedCollectionEntry $curatedCollectionEntry)
    {
        return (new CuratedCollectionEntryEditResource($curatedCollectionEntry))
            ->blueprint($curatedCollection->addEntryBlueprint());
    }

    public function update(CuratedCollectionEntryUpdateRequest $request, CuratedCollection $curatedCollection, CuratedCollectionEntry $curatedCollectionEntry)
    {
        $entry  = $curatedCollectionEntry->entry();

        $inputData = $request->all();

        /** @var \Statamic\Fields\Blueprint $blueprint */
        $blueprint = $curatedCollection->addEntryBlueprint();
        $fields = $blueprint->fields()->addValues($inputData);
        $data = $fields->validate();

        $curatedCollectionEntry->data(collect($data)->except(['curated_collection', 'entry', 'order', 'unpublish_at']));


        if ($entry->status() === 'published') {

            if ($request->has('order'))
            {
                // Reorder
                $curatedCollectionEntry->setPosition($request->input('order'));
            }
        } else {
            // Set wanted publish order. Null to add to the bottom of the list.
            $curatedCollectionEntry->publish_order = $data['publish_order'] ?? null;

            // Override the automated expiration time
            $curatedCollectionEntry->expiration_time = $data['expiration_time'] ?? null;
        }

        // set the unpublish time absolute
        if ($data['unpublish_at']) {
            $curatedCollectionEntry->unpublishAt($data['unpublish_at']);
        }

        $curatedCollectionEntry->save();

        return new CuratedCollectionEntryResource($curatedCollectionEntry);
    }

    public function destroy(Request $request, CuratedCollection $curatedCollection, CuratedCollectionEntry $curatedCollectionEntry)
    {
        $curatedCollectionEntry->delete();

        // Update the order indexes after deleting an entry
        $curatedCollectionEntry->curatedCollection->reorderEntries();

        return response()->json(['success' => true]);
    }
}
