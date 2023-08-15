<?php

namespace Tv2regionerne\StatamicCuratedCollection\Http\Controllers\CP;

use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Controllers\CP\Fields\ManagesBlueprints;
use Tv2regionerne\StatamicCuratedCollection\Models\CuratedCollection;

class CuratedCollectionBlueprintController extends CpController
{

    use ManagesBlueprints;

    public function __construct(Request $request)
    {
        $this->middleware(\Illuminate\Auth\Middleware\Authorize::class.':configure fields');
        parent::__construct($request);
    }

    public function edit($curatedCollection)
    {
        if (! $curatedCollection = CuratedCollection::findByHandle($curatedCollection)) {
            return $this->pageNotFound();
        }

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

        $request->validate(['tabs' => 'array']);

        $this->updateBlueprint($request, $curatedCollection->blueprint());
    }

}
