<?php

namespace Tv2regionerne\StatamicCuratedCollection\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EntryRelationsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $data = $this->resource['curatedCollections']
            ->map(function ($curatedCollection) {
                return [
                    'curatedCollection' => $curatedCollection,
                    'entry' => $this->resource['entries']->first(function ($entry) use ($curatedCollection) {
                        return $entry->curated_collection_id === $curatedCollection->id;
                    }),
                ];
            });

        return [
            'collection' => $this->resource['collection'],
            'curatedCollections' => $this->resource['curatedCollections'],
            'entries' => $this->resource['entries'],
            'id' => $this->resource['id'],
            'data' => $data,
            'meta' => [],
        ];
    }
}
