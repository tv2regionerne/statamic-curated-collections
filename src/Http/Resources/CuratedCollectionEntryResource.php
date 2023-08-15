<?php

namespace Tv2regionerne\StatamicCuratedCollection\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Statamic\Entries\Collection;
use Statamic\Facades\Entry;
use Statamic\Http\Resources\API\EntryResource;

class CuratedCollectionEntryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $entry = $this->entry();
        return [
            'id' => $this->id,
            'curated_collection_id' => $this->curated_collection_id,
            'collection' => $this->collection,
            'entry_id' => $this->entry_id,
            'entry' => [
                new EntryResource($entry),
            ],
            'site' => $this->site,
            'status' => $this->status,
            'order' => $this->order_column,
            'publish_order' => $this->publish_order,
            'expiration_time' => $this->expiration_time,
            'data' => $this->data,
            'unpublish_at' => $this->unpublish_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
