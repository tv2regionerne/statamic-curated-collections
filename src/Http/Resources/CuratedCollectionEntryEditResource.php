<?php

namespace Tv2regionerne\StatamicCuratedCollection\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Statamic\Entries\Collection;
use Statamic\Facades\Entry;
use Statamic\Http\Resources\API\EntryResource;

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
        $values = array_merge((array) $this->data, [
            'id' => $this->id,
            'entry' => $this->entry_id,
            'curated_collection_id' => $this->curated_collection_id,
            'collection' => $this->collection,
            'order' => $this->order_column,
            'unpublish_at' => $this->unpublish_at,
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
