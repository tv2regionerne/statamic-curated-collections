<?php

namespace Tv2regionerne\StatamicCuratedCollection\Http\Controllers\Api;

use Illuminate\Http\Request;
use Statamic\Http\Controllers\API\ApiController;
use Tv2regionerne\StatamicCuratedCollection\Http\Controllers\CP\CuratedCollectionController as CpController;
use Tv2regionerne\StatamicCuratedCollection\Models\CuratedCollection;
use Tv2regionerne\StatamicPrivateApi\Traits\VerifiesPrivateAPI;

class CuratedCollectionController extends ApiController
{
    use VerifiesPrivateAPI;

    public function index(Request $request)
    {
        return (new CpController($request))->index($request);
    }

    public function show(Request $request, $id)
    {
        return (new CpController($request))->show($request, $id);
    }

    public function store(Request $request)
    {
        return (new CpController($request))->store($request);
    }

    public function update(Request $request, $id)
    {
        $curatedCollection = CuratedCollection::findByHandle($id);

        $this->abortIfInvalid($curatedCollection);

        $blueprint = (new CpController($request))->editFormBlueprint($curatedCollection);

        // cp controller expects the full payload, so merge with existing values
        $mergedData = $this->mergeBlueprintAndRequestData($blueprint, $curatedCollection, $request);

        $request->merge($mergedData->all());

        return (new CpController($request))->update($request, $id);
    }

    public function destroy(Request $request, $id)
    {
        $curatedCollection = CuratedCollection::findByHandle($id);

        $this->abortIfInvalid($curatedCollection);

        return (new CpController($request))->destroy($id);
    }

    private function abortIfInvalid($handler)
    {
        if (! $handler) {
            response()->json(['error' => true, 'message' => 'Not found'], 404)->send();
        }
    }
}
