<?php

namespace Tv2regionerne\StatamicCuratedCollection\Http\Controllers\CP;

use Illuminate\Http\Request;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Tv2regionerne\StatamicCuratedCollection\Models\CuratedCollection;

class CuratedCollectionController extends CpController
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
                'editable' => User::current()->can('update', $curatedCollection),
                'blueprint_editable' => User::current()->can('update', $curatedCollection),
                'deleteable' => User::current()->can('delete', $curatedCollection),
            ];
        })->values();

        return view('statamic-curated-collections::curated-collections.index', compact('curatedCollections'));
    }

    public function edit($curatedCollection)
    {
        $curatedCollection = CuratedCollection::findByHandle($curatedCollection);

        $this->authorize('update', $curatedCollection, __('You are not authorized to configure curated collections.'));

        $values = [
            'title' => $curatedCollection->title,
            'handle' => $curatedCollection->handle,
            'collections' => $curatedCollection->collections,
            'max_items' => $curatedCollection->max_items,
            'display_form' => $curatedCollection->display_form,
            'fallback_collection' => $curatedCollection->fallback_collection,
            'fallback_sort_field' => $curatedCollection->fallback_sort_field,
            'fallback_sort_direction' => $curatedCollection->fallback_sort_direction,
            'automation' => $curatedCollection->automation,
            'update_expiration_on_publish' => $curatedCollection->update_expiration_on_publish,
            'expiration_time' => $curatedCollection->expiration_time,
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
        abort_if(! $curatedCollection = CuratedCollection::query()->where('handle', $curatedCollection)->first(), 404);

        $this->authorize('view', $curatedCollection, __('You are not authorized to view navs.'));

        if (!$curatedCollection->collections) {
            redirect(cp_route('curated-collections.edit', $curatedCollection->handle))
                ->with('error', __('statamic-curated-collections::configure.collections_instructions'))
                ->send();
        }

        $blueprint = $curatedCollection->addEntryBlueprint();

        $fields = $blueprint->fields();

        $defaults = $fields->all()->map(function ($field) {
            return $field->fieldtype()->preProcess($field->defaultValue());
        });

        return view('statamic-curated-collections::curated-collections.show', [
            'curatedCollection' => $curatedCollection,
            'collections' => $curatedCollection->collections,
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

        return [
            'title' => $curatedCollection->title,
        ];
    }

    public function store(Request $request)
    {
        $this->authorize('create', CuratedCollection::class, __('You are not authorized to create curated collections.'));

        $values = $request->validate([
            'title' => 'required',
            'handle' => 'required|alpha_dash',
        ]);

        $curatedCollection = CuratedCollection::make();
        $curatedCollection->fill($values);
        $curatedCollection->site = Site::selected()->handle();
        $curatedCollection->save();

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
                        'instructions' => __('statamic-curated-collections::configure.title_instructions'),
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
                        'instructions' => __('statamic-curated-collections::configure.blueprint_instructions'),
                        'html' => ''.
                            '<div class="text-xs">'.
                            '   <a href="'.cp_route('curated-collections.blueprint.edit', $curatedCollection->handle).'" class="text-blue">'.__('Edit').'</a>'.
                            '</div>',
                    ],
                    'collections' => [
                        'display' => __('Collections'),
                        'instructions' => __('statamic-curated-collections::configure.collections_instructions'),
                        'type' => 'collections',
                        'mode' => 'select',
                        'validate' => [
                            'required',
                            'min:1'
                        ],
                    ],
                    'display_form' => [
                        'display' => __('Display form'),
                        'instructions' => __('statamic-curated-collections::configure.display_form'),
                        'type' => 'toggle',
                        'default' => true,
                    ],
                    'max_entries' => [
                        'display' => __('Max Entries'),
                        'instructions' => __('statamic-curated-collections::configure.max_entries_instructions'),
                        'type' => 'integer',
                        'validate' => 'min:0',
                    ],

                ],
            ],
            'automation' => [
                'display' => __('Automation'),
                'fields' => [
                    'automation' => [
                        'display' => __('Automation activated'),
                        'instructions' => __('statamic-curated-collections::configure.automation_instructions'),
                        'type' => 'toggle',
                        'default' => true,
                    ],
                    'update_expiration_on_publish' => [
                        'display' => __('Update expiration time on publish'),
                        'instructions' => __('statamic-curated-collections::configure.automation_update_expiration_instruction'),
                        'type' => 'toggle',
                        'default' => true,
                    ],
                    'expiration_time' => [
                        'display' => __('Expiration time in hours'),
                        'instructions' => __('statamic-curated-collections::configure.automation_expiration_time_instruction'),
                        'type' => 'integer',
                        'default' => 96,
                    ],
                ],
            ],
            'fallback' => [
                'display' => __('Tag data fallback'),
                'fields' => [
                    'fallback_collection' => [
                        'display' => __('Fallback Collections'),
                        'instructions' => __('statamic-curated-collections::configure.fallback_collection_instructions'),
                        'type' => 'collections',
                        'mode' => 'select',
                        'max_items' => 1,
                    ],
                    'fallback_sort_field' => [
                        'display' => __('Fallback Sort field'),
                        'instructions' => __('statamic-curated-collections::configure.fallback_sort_field'),
                        'type' => 'text',
                        'default' => 'date',
                    ],
                    'fallback_sort_direction' => [
                        'display' => __('Fallback Sort direction'),
                        'type' => 'radio',
                        'inline' => true,
                        'default' => 'desc',
                        'options' => [
                            'asc' => __('Ascending'),
                            'desc' => __('Descending'),
                        ],
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
    }

}
