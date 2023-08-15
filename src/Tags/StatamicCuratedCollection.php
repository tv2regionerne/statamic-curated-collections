<?php

namespace Tv2regionerne\StatamicCuratedCollection\Tags;

use Statamic\Facades\Entry;
use Statamic\Tags\Tags;
use Tv2regionerne\StatamicCuratedCollection\Models\CuratedCollection;
use Tv2regionerne\StatamicCuratedCollection\Facades\CuratedCollection as CuratedCollectionFacade;
use Tv2regionerne\StatamicCuratedCollection\Facades\CuratedCollectionEntry as CuratedCollectionEntryFacade;
use Tv2regionerne\StatamicCuratedCollection\Models\CuratedCollectionEntry;

class StatamicCuratedCollection extends Tags
{
    protected static $handle = 'curated_collection';

    /**
     * The {{ curated_collection:* }} tag.
     *
     * @return array
     */
    public function wildcard($tag)
    {

        $curatedCollection = CuratedCollectionFacade::findByHandle($tag);

        $fallback = $this->params->get('fallback', false);
        $limit = $this->params->get('limit', 10);

        $entries = [];
        $ids = [];
        $query = CuratedCollectionEntryFacade::query()
            ->where('curated_collection', $tag)
            ->orderBy('order', 'asc')
            ->published()
            ->limit($limit);

        $query->get()->each(function($entry) use (&$entries, &$ids) {
            $e = Entry::find($entry->entry);
            if (!$e) {
                return;
            }

            if ($e->published() === false) {
                return;
            }

            $ids[] = $e->id();
            $entries[] = [
                'entry' => $e,
                'order' => $entry->order(),
                'type' => 'curated'
            ];
        });

        if ($fallback && count($entries) < $limit) {
            $fallbackEntries = Entry::query()
                ->where('collection', $curatedCollection->fallback_collection)
                ->whereNotIn('id', $ids)
                ->limit($limit - count($entries))
                ->orderBy($curatedCollection->fallback_sort_field, $curatedCollection->fallback_sort_direction)
                ->get();
            $fallbackEntries->transform(function ($e) {
                return [
                    'entry' => $e,
                    'type' => 'fallback'
                ];
            });
            $entries = array_merge($entries, $fallbackEntries->all());
        }

        return $entries;
    }
}
