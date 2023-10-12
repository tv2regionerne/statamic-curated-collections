<?php

namespace Tv2regionerne\StatamicCuratedCollection\Http\Controllers\CP;

use Illuminate\Http\Request;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Tv2regionerne\StatamicCuratedCollection\Events\CuratedCollectionTagEvent;
use Tv2regionerne\StatamicCuratedCollection\Events\CuratedCollectionUpdatedEvent;
use Tv2regionerne\StatamicCuratedCollection\Models\CuratedCollection;

class CuratedCollectionEntriesController extends CpController
{

    public function index()
    {
        $this->authorize('viewAny', CuratedCollection::class, __('You are not authorized to view curated collections.'));

        $curatedCollections = CuratedCollection::all()->filter(function ($curatedCollection) {
            return User::current()->can('view', $curatedCollection);
        })->map(function ($curatedCollection) {
            return [
                'id' => $curatedCollection->handle,
                'title' => $curatedCollection->title,
                'show_url' => $curatedCollection->showUrl(),
                'edit_url' => $curatedCollection->editUrl(),
                'delete_url' => $curatedCollection->deleteUrl(),
                'blueprints_url' => $curatedCollection->blueprintUrl(),
                'editable' => User::current()->can('edit', $curatedCollection),
                'blueprint_editable' => User::current()->can('edit blueprint', $curatedCollection),
                'deleteable' => User::current()->can('delete', $curatedCollection),
            ];
        })->values();

        return view('statamic-curated-collections::curated-collections.index', compact('curatedCollections'));
    }

    public function edit($curatedCollection)
    {
        $curatedCollection = CuratedCollection::findByHandle($curatedCollection);

        $this->authorize('edit', $curatedCollection, __('You are not authorized to configure navs.'));

        $values = [
            'title' => $curatedCollection->title,
            'handle' => $curatedCollection->handle,
            'collections' => json_decode($curatedCollection->collections),
            'max_items' => $curatedCollection->max_items,
        ];

        $fields = ($blueprint = $this->editFormBlueprint($curatedCollection))
            ->fields()
            ->addValues($values)
            ->preProcess();

        return view('statamic-curated-collections::curated-collections.edit', [
            'blueprint' => $blueprint->toPublishArray(),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
            'curatedCollection' => $curatedCollection,
        ]);
    }

    public function show(Request $request, $curatedCollection)
    {
        abort_if(! $curatedCollection = CuratedCollection::where('handle', $curatedCollection)->first(), 404);

        $this->authorize('view', $curatedCollection, __('You are not authorized to view navs.'));

        $entries = $curatedCollection->entries;

        return view('statamic-curated-collections::curated-collections.show', [
            'curatedCollection' => $curatedCollection,
            'collections' => $curatedCollection->collections,
            'blueprint' => $curatedCollection->blueprint()->toPublishArray(),
            'entries' => $entries,
        ]);
    }

    public function create()
    {
        $this->authorize('create', CuratedCollection::class, __('You are not authorized to configure curated collections.'));

        return view('statamic-curated-collections::curated-collections.create');
    }

    public function update(Request $request, $curatedCollection)
    {
        $curatedCollection = CuratedCollection::findByHandle($curatedCollection);

        $this->authorize('update', $curatedCollection, __('You are not authorized to configure curated collections.'));

        $fields = $this->editFormBlueprint($curatedCollection)->fields()->addValues($request->all());

        $fields->validate();

        $values = $fields->process()->values()->all();

        $curatedCollection->fill($values);
        $curatedCollection->save();

        CuratedCollectionUpdatedEvent::dispatch($curatedCollection->handle);

        return [
            'title' => $curatedCollection->title,
        ];
    }

    public function store(Request $request)
    {
        $this->authorize('store', CuratedCollection::class, __('You are not authorized to create curated collections.'));

        $values = $request->validate([
            'title' => 'required',
            'handle' => 'required|alpha_dash',
        ]);

        $curatedCollection = new CuratedCollection();
        $curatedCollection->fill($values);
        $curatedCollection->site = Site::selected()->handle();
        $curatedCollection->save();

        CuratedCollectionUpdatedEvent::dispatch($curatedCollection->handle);

        return ['redirect' => $curatedCollection->showUrl()];
    }

    public function editFormBlueprint($curatedCollection)
    {
        $contents = [
            'name' => [
                'display' => __('Name'),
                'fields' => [
                    'title' => [
                        'display' => __('Title'),
                        'instructions' => __('statamic-curated-collections::messages.configure_title_instructions'),
                        'type' => 'text',
                        'validate' => 'required',
                    ],
                ],
            ],
            'options' => [
                'display' => __('Options'),
                'fields' => [
                    'blueprint' => [
                        'type' => 'html',
                        'instructions' => __('statamic::messages.navigation_configure_blueprint_instructions'),
                        'html' => ''.
                            '<div class="text-xs">'.
                            '   <a href="'.cp_route('curated-collections.blueprint.edit', $curatedCollection->handle).'" class="text-blue">'.__('Edit').'</a>'.
                            '</div>',
                    ],
                    'collections' => [
                        'display' => __('Collections'),
                        'instructions' => __('statamic::messages.navigation_configure_collections_instructions'),
                        'type' => 'collections',
                        'mode' => 'select',
                    ],
                    'max_entries' => [
                        'display' => __('Max Entries'),
                        'instructions' => __('statamic::messages.max_depth_instructions'),
                        'type' => 'integer',
                        'validate' => 'min:0',
                    ],
                ],
            ],
        ];

        if (Site::hasMultiple()) {
            $contents['options']['fields']['sites'] = [
                'display' => __('Sites'),
                'type' => 'sites',
                'mode' => 'select',
                'required' => true,
            ];
        }

        return Blueprint::makeFromTabs($contents);
    }

    public function destroy($curatedCollection)
    {
        $curatedCollection = CuratedCollection::findByHandle($curatedCollection);

        $this->authorize('delete', $curatedCollection, __('You are not authorized to delete curated collections.'));

        $curatedCollection->delete();
        CuratedCollectionUpdatedEvent::dispatch($curatedCollection->handle);
    }

}
