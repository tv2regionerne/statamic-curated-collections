<?php

namespace Tv2regionerne\StatamicCuratedCollection\Data;

use Statamic\Events\NavBlueprintFound;
use Statamic\Facades\Blueprint;
use Tv2regionerne\StatamicCuratedCollection\Data\Traits\Fillable;
use Tv2regionerne\StatamicCuratedCollection\Facades\CuratedCollection as CuratedCollectionFacade;
use Statamic\Data\ContainsData;
use Statamic\Data\ExistsAsFile;
use Statamic\Data\TracksQueriedColumns;
use Statamic\Facades\Stache;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class CuratedCollection
{
    use FluentlyGetsAndSets, ExistsAsFile, TracksQueriedColumns, ContainsData, Fillable;

    public $id;
    public string $title;
    public string $handle;
    public ?string $site;
    public array $collections = [];
    public ?int $max_items = null;
    public ?string $fallback_collection = null;
    public ?string $fallback_sort_field = null;
    public ?string $fallback_sort_direction = 'desc';
    public bool $automation = true;
    public bool $update_expiration_on_publish = true;
    public int $expiration_time = 96;


    protected array $selectedQueryRelations = [];

    public function __construct()
    {
        $this->data = collect();
    }

    public function id($id = null)
    {
        return $this
            ->fluentlyGetOrSet('id')
            ->args(func_get_args());
    }

    public function handle($handle = null)
    {
        return $this
            ->fluentlyGetOrSet('handle')
            ->args(func_get_args());
    }

    public function title($title = null)
    {
        return $this
            ->fluentlyGetOrSet('title')
            ->args(func_get_args());
    }

    public function collections($collections = [])
    {
        return $this
            ->fluentlyGetOrSet('collections')
            ->args(func_get_args());
    }

    public function entries()
    {
        return collect();
    }

    public function blueprint()
    {
        $blueprint = Blueprint::find('curated-collection.'.$this->handle)
            ?? Blueprint::makeFromFields([])->setHandle($this->handle)->setNamespace('curated-collection');

        NavBlueprintFound::dispatch($blueprint, $this);

        return $blueprint;
    }

    public function addEntryBlueprint()
    {
        $blueprint = $this->blueprint();
        if (!$blueprint->hasField('entry'))
        {
            $blueprint->ensureField('unpublish_at', [
                'type' => 'date',
                'display' => __('Unpublish at'),
                'instructions' => __('Time to unpublish the entry in the curated collection.'),
                'time_enabled' => true,
                'format' => 'c',
                'width' => 50,
                'validate' => [
                    'nullable',
                ]
            ], null, true);
            $blueprint->ensureField('entry', [
                'type' => 'entries',
                'display' => __('Entry'),
                'mode' => 'default',
                'collections' => $this->collections,
                'max_items' => 1,
                'create' => false,
                'validate' => [
                    'required',
                ]
            ], null, true);

        }

        return $blueprint;
    }

    public function path()
    {
        return Stache::store('curated-collections')->directory().str_slug($this->handle()).'.yaml';
    }

    public function toArray(): array
    {
        return array_merge($this->data()->toArray(), [
            'id' => $this->id,
            'title' => $this->title,
            'handle' => $this->handle,
            'collections' => $this->collections,
            'max_items' => $this->max_items,
            'fallback_collection' => $this->fallback_collection,
            'fallback_sort_field' => $this->fallback_sort_field,
            'fallback_sort_direction' => $this->fallback_sort_direction,
            'automation' => $this->automation,
            'update_expiration_on_publish' => $this->update_expiration_on_publish,
            'expiration_time' => $this->expiration_time,
        ]);
    }

    public function toResource()
    {
        return $this->toArray();
    }

    public function fileData()
    {
        return $this->toArray();
    }

    public function selectedQueryRelations($relations)
    {
        $this->selectedQueryRelations = $relations;

        return $this;
    }

    public function save(): self
    {
        if (! $this->id) {
            $this->id = app('stache')->generateId();
        }

        if (method_exists($this, 'beforeSaved')) {
            $this->beforeSaved();
        }

        CuratedCollectionFacade::save($this);

        if (method_exists($this, 'afterSaved')) {
            $this->afterSaved();
        }

        return $this;
    }

    public function delete(): void
    {
        CuratedCollectionFacade::delete($this);
    }

    public function showUrl()
    {
        return cp_route('curated-collections.show', $this->handle);
    }

    public function editUrl()
    {
        return cp_route('curated-collections.edit', $this->handle);
    }

    public function deleteUrl()
    {
        return cp_route('curated-collections.destroy', $this->handle);
    }

    public function blueprintUrl()
    {
        return cp_route('curated-collections.blueprint.edit', $this->handle);
    }
}
