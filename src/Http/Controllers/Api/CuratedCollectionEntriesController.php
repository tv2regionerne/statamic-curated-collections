<?php

namespace Tv2regionerne\StatamicCuratedCollection\Http\Controllers\Api;

use Illuminate\Http\Request;
use Statamic\Http\Controllers\API\ApiController;
use Tv2regionerne\StatamicCuratedCollection\Http\Controllers\CP\ApiEntriesController as CpController;
use Tv2regionerne\StatamicCuratedCollection\Http\Requests\CuratedCollectionEntryIndexRequest;
use Tv2regionerne\StatamicCuratedCollection\Http\Requests\CuratedCollectionEntryReorderRequest;
use Tv2regionerne\StatamicCuratedCollection\Http\Requests\CuratedCollectionEntryStoreRequest;
use Tv2regionerne\StatamicCuratedCollection\Http\Requests\CuratedCollectionEntryUpdateRequest;
use Tv2regionerne\StatamicCuratedCollection\Http\Resources\CuratedCollectionEntryEditResource;
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

        $curatedCollectionEntry = CuratedCollectionEntry::find($entry);

        $this->abortIfInvalid($curatedCollectionEntry);

        $originalData = (new CuratedCollectionEntryEditResource($curatedCollectionEntry))
            ->blueprint($curatedCollection->addEntryBlueprint())
            ->toArray($request)['values'];
        $originalData = $originalData->merge($request->all());

        $request->merge($originalData->all());

        return (new CpController($request))->update($request, $curatedCollection, $curatedCollectionEntry);
    }

    public function destroy(Request $request, $id, $entry)
    {
        $curatedCollection = CuratedCollection::findByHandle($id);

        $this->abortIfInvalid($curatedCollection);

        $curatedCollectionEntry = CuratedCollectionEntry::find($entry);

        return (new CpController($request))->destroy($request, $curatedCollection, $curatedCollectionEntry);
    }

    public function destroyAll(Request $request, $id)
    {
        $curatedCollection = CuratedCollection::findByHandle($id);

        $this->abortIfInvalid($curatedCollection);

        return (new CpController($request))->destroyAll($request, $curatedCollection);
    }

    private function abortIfInvalid($handler)
    {
        if (! $handler) {
            response()->json(['error' => true, 'message' => 'Not found'], 404)->send();
        }
    }
}
