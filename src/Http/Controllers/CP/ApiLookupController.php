<?php

namespace Tv2regionerne\StatamicCuratedCollection\Http\Controllers\CP;

use Statamic\Http\Controllers\CP\Fieldtypes\RelationshipFieldtypeController;
use Statamic\Http\Requests\FilteredRequest;
use Tv2regionerne\StatamicCuratedCollection\Http\Resources\EntryResource;

class ApiLookupController extends RelationshipFieldtypeController
{
    public function index(FilteredRequest $request)
    {
        $fieldtype = $this->fieldtype($request);

        $items = $fieldtype->getIndexItems($request);

        if ($items instanceof Collection) {
            $items = $fieldtype->filterExcludedItems($items, $request->exclusions ?? []);
        }

        return EntryResource::collection($items)
            ->additional($fieldtype->getResourceCollection($request, $items)->additional);
    }

}
