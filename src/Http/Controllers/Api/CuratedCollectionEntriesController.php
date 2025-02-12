<?php

namespace Tv2regionerne\StatamicCuratedCollection\Http\Controllers\Api;

use Illuminate\Http\Request;
use Statamic\Http\Controllers\API\ApiController;
use Tv2regionerne\StatamicCuratedCollection\Http\Controllers\CP\ApiEntriesController as CpController;
use Tv2regionerne\StatamicCuratedCollection\Http\Requests\CuratedCollectionEntryIndexRequest;
use Tv2regionerne\StatamicCuratedCollection\Http\Requests\CuratedCollectionEntryReorderRequest;
use Tv2regionerne\StatamicCuratedCollection\Http\Requests\CuratedCollectionEntryStoreRequest;
use Tv2regionerne\StatamicCuratedCollection\Http\Requests\CuratedCollectionEntryUpdateRequest;
use Tv2regionerne\StatamicCuratedCollection\Models\CuratedCollection;
use Tv2regionerne\StatamicCuratedCollection\Models\CuratedCollectionEntry;
use Tv2regionerne\StatamicPrivateApi\Traits\VerifiesPrivateAPI;

class CuratedCollectionEntriesController extends ApiController
{
    use VerifiesPrivateAPI;

    public function index(CuratedCollectionEntryIndexRequest $request, $id)
    {
        $curatedCollection = CuratedCollection::findByHandle($id);

        $this->abortIfInvalid($curatedCollection);

        return (new CpController($request))->index($request, $curatedCollection);
    }

    public function reorder(CuratedCollectionEntryReorderRequest $request, $id)
    {
        $curatedCollection = CuratedCollection::findByHandle($id);

        $this->abortIfInvalid($curatedCollection);

        return (new CpController($request))->reorder($request, $curatedCollection);
    }

    public function store(CuratedCollectionEntryStoreRequest $request, $id)
    {
        $curatedCollection = CuratedCollection::findByHandle($id);

        $this->abortIfInvalid($curatedCollection);

        return (new CpController($request))->store($request, $curatedCollection);
    }

    public function update(CuratedCollectionEntryUpdateRequest $request, $id, $entry)
    {
        $curatedCollection = CuratedCollection::findByHandle($id);

        $this->abortIfInvalid($curatedCollection);

        $blueprint = $curatedCollection->addEntryBlueprint();

        $curatedCollectionEntry = CuratedCollectionEntry::find($entry);

        $this->abortIfInvalid($curatedCollectionEntry);

        // cp controller expects the full payload, so merge with existing values
        $mergedData = $this->mergeBlueprintAndRequestData($blueprint, $curatedCollectionEntry, $request);

        $request->merge($mergedData->all());

        return (new CpController($request))->update($request, $curatedCollection, $curatedCollectionEntry);
    }

    public function destroy(Request $request, $id, $entry)
    {
        $curatedCollection = CuratedCollection::findByHandle($id);

        $this->abortIfInvalid($curatedCollection);

        $curatedCollectionEntry = CuratedCollectionEntry::find($entry);

        return (new CpController($request))->destroy($request, $curatedCollection, $curatedCollectionEntry);
    }

    private function abortIfInvalid($handler)
    {
        if (! $handler) {
            response()->json(['error' => true, 'message' => 'Not found'], 404)->send();
        }
    }
}
