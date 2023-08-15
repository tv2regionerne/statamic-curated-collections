<?php

namespace Tv2regionerne\StatamicCuratedCollection\Data;

use Carbon\Carbon;
use Statamic\Contracts\Data\Augmented;
use Statamic\Data\HasAugmentedInstance;
use Statamic\Entries\Collection;
use Statamic\Entries\Entry;
use Statamic\Facades\Blink;
use Tv2regionerne\StatamicCuratedCollection\Data\Traits\Fillable;
use Tv2regionerne\StatamicCuratedCollection\Facades\CuratedCollectionEntry as CuratedCollectionEntryFacade;
use Statamic\Data\ContainsData;
use Statamic\Data\ExistsAsFile;
use Statamic\Data\TracksQueriedColumns;
use Statamic\Facades\Stache;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class CuratedCollectionEntry
{
    use FluentlyGetsAndSets, ExistsAsFile, TracksQueriedColumns, HasAugmentedInstance, ContainsData, Fillable;

    public $id;
    public ?string $curated_collection;
    public ?string $collection;
    public ?string $entry;
    public int $order = 0;
    public $unpublish_at;


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

    public function curatedCollection($curatedCollection = null)
    {
        return $this
            ->fluentlyGetOrSet('curated_collection')
            ->setter(function ($value) {
                return $value instanceof CuratedCollection ? $value->handle(): $value;
            })
            ->args(func_get_args());
    }

    public function entry($entry = [])
    {
        return $this
            ->fluentlyGetOrSet('entry')
            ->setter(function ($value) {
                return $value instanceof Entry ? $value->id() : $value;
            })
            ->args(func_get_args());
    }

    public function collection($collection = null)
    {
        return $this
            ->fluentlyGetOrSet('collection')
            ->setter(function ($value) {
                return $value instanceof Collection ? $value->handle(): $value;
            })
            ->args(func_get_args());
    }

    public function order($order = 0)
    {
        return $this
            ->fluentlyGetOrSet('order')
            ->args(func_get_args());
    }

    public function unpublishAt(?Carbon $unpublish_at = null)
    {
        return $this
            ->fluentlyGetOrSet('unpublish_at')
            ->setter(function ($value) {
                return $value instanceof Carbon ? $value->format('c') : $value;
            })
            ->getter(function ($value) {
                return $value ? Carbon::parse($value) : null;
            })
            ->args(func_get_args());
    }

    public function blueprint()
    {
        $curatedCollectionHandle = $this->curatedCollection();
        $curatedCollection = \Tv2regionerne\StatamicCuratedCollection\Facades\CuratedCollection::findByHandle($curatedCollectionHandle);
        return $curatedCollection->blueprint();
    }

    public function path()
    {
        return vsprintf('%s/%s/%s.%s', [
            rtrim(Stache::store('curated-collections')->directory(), '/'),
            $this->curatedCollection(),
            $this->id(),
            $this->fileExtension(),
        ]);
    }

    public function toArray(): array
    {
        return array_merge($this->data()->toArray(), [
            'id' => $this->id,
            'entry' => $this->entry(),
            'curated_collection' => $this->curatedCollection(),
            'collection' => $this->collection(),
            'order' => $this->order(),
            'unpublish_at' => $this->unpublishAt(),
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

        CuratedCollectionEntryFacade::save($this);

        if (method_exists($this, 'afterSaved')) {
            $this->afterSaved();
        }

        return $this;
    }

    public function delete(): void
    {
        CuratedCollectionEntryFacade::delete($this);
    }

    public function newAugmentedInstance(): Augmented
    {
        return new AugmentedEntry($this);
    }
}
