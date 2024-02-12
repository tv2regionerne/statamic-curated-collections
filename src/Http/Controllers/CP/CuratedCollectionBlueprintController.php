<?php

namespace Tv2regionerne\StatamicCuratedCollection\Http\Controllers\CP;

use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Controllers\CP\Fields\ManagesBlueprints;
use Tv2regionerne\StatamicCuratedCollection\Models\CuratedCollection;

class CuratedCollectionBlueprintController extends CpController
{
    use ManagesBlueprints;

    public function edit($curatedCollection)
    {
        if (! $curatedCollection = CuratedCollection::findByHandle($curatedCollection)) {
            return $this->pageNotFound();
        }

        $this->authorize('update', $curatedCollection, __('You are not authorized to configure curated collections.'));

        $blueprint = $curatedCollection->blueprint();

        return view('statamic-curated-collections::curated-collections.blueprints.edit', [
            'curatedCollection' => $curatedCollection,
            'blueprint' => $blueprint,
            'blueprintVueObject' => $this->toVueObject($blueprint),
        ]);
    }

    public function update(Request $request, $curatedCollection)
    {
        if (! $curatedCollection = CuratedCollection::findByHandle($curatedCollection)) {
            return $this->pageNotFound();
        }

        $this->authorize('update', $curatedCollection, __('You are not authorized to configure curated collections.'));

        $request->validate(['tabs' => 'array']);

        $this->updateBlueprint($request, $curatedCollection->blueprint());
    }
}
