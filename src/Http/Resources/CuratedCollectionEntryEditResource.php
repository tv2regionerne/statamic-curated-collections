<?php

namespace Tv2regionerne\StatamicCuratedCollection\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CuratedCollectionEntryEditResource extends JsonResource
{
    protected $blueprint;

    public function blueprint($blueprint)
    {
        $this->blueprint = $blueprint;

        return $this;
    }

    public function toArray($request)
    {
        $entry = $this->resource->entry();
        $entry_published = $entry->published && (! $entry->published_date || $entry->published_date->isPast());

        $data = json_decode(json_encode($this->data), true) ?? [];
        $values = array_merge($data, [
            'id' => $this->id,
            'entry' => $this->entry_id,
            'curated_collection_id' => $this->curated_collection_id,
            'collection' => $this->collection,
            'order' => $this->order_column,
            'unpublish_at' => $this->unpublish_at,
            'entry_published' => $entry_published,
        ]);

        $fields = $this->blueprint
            ->fields()
            ->addValues($values)
            ->preProcess();

        return [
            'values' => $fields->values(),
            'meta' => $fields->meta(),
            'blueprint' => $this->blueprint->toPublishArray(),
        ];
    }
}
