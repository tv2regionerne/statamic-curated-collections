<?php

namespace Tv2regionerne\StatamicCuratedCollection\Fieldtypes;

use Statamic\CP\Column;
use Statamic\Fieldtypes\Relationship;
use Statamic\Support\Arr;

class CuratedCollection extends Relationship
{
    protected $canCreate = false;

    protected static $title = 'Curated Collections';

    protected $icon = 'addons';

    public function getIndexItems($request)
    {
        return \Tv2regionerne\StatamicCuratedCollection\Models\CuratedCollection::all()
            ->map(function ($collection) {
                return [
                    'id' => $collection->id,
                    'title' => $collection->title,
                ];
            })
            ->values();
    }

    protected function getColumns()
    {
        return [
            Column::make('title')
                ->label(__('Title')),
        ];
    }

    public function toItemArray($id)
    {
        $collection = \Tv2regionerne\StatamicCuratedCollection\Models\CuratedCollection::find($id);

        if (! $collection) {
            return $this->invalidItemArray($id);
        }

        return [
            'id' => $collection->id,
            'title' => $collection->title,
        ];
    }

    public function preProcessIndex($data)
    {
        if (! $data) {
            return;
        }

        return \Tv2regionerne\StatamicCuratedCollection\Models\CuratedCollection::query()
            ->whereIn('id', Arr::wrap($data))
            ->get()
            ->map(function ($collection) {
                return [
                    'id' => $collection->id,
                    'title' => $collection->title,
                ];
            })
            ->values();
    }

    public function augment($value)
    {
        $query = \Tv2regionerne\StatamicCuratedCollection\Models\CuratedCollection::query()->whereIn('id', Arr::wrap($value));

        return $this->config('max_items') === 1 ? $query->first() : $query;
    }
}
