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
use Tv2regionerne\StatamicCuratedCollection\Http\Resources\EntryRelationsResource;
use Tv2regionerne\StatamicCuratedCollection\Models\CuratedCollection;
use Tv2regionerne\StatamicCuratedCollection\Models\CuratedCollectionEntry;

class ApiEntryRelationController
{

    public function index(Request $request, $id)
    {
        // resolve the entry id to a model. Unsure about model binding here, so simple solution
        $entry = Entry::find($id);
        if (!$entry) {
            abort(404);
        }

        // collection handle
        $handle = $entry->collection->handle;

        // find curated collections which contain the collection handle
        $curatedCollections = CuratedCollection::where('collections', 'like', '%"'. $entry->collection->handle .'"%')->get();

        // Fetch entries
        $entries = CuratedCollectionEntry::query()
            ->where('entry_id', $entry->id())
            ->get();

        // Return object
        return new EntryRelationsResource([
            'collection' => $handle,
            'curatedCollections' => $curatedCollections,
            'entries' => $entries,
            'id' => $entry->id,
        ]);
    }
}
