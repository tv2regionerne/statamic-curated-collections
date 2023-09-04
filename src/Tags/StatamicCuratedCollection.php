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

        $curatedCollection = CuratedCollection::where('handle', $tag)->first();
        if (!$curatedCollection) {
            return null;
        }

        $fallback = $this->params->get('fallback', false);
        $limit = $this->params->get('limit', 10);

        $entries = [];
        $ids = [];
        $query = CuratedCollectionEntry::query()
            ->where('curated_collection_id', $curatedCollection->id)
            ->where('status', 'published')
            ->ordered()
            ->limit($limit);

        $query->get()->each(function(CuratedCollectionEntry $entry) use (&$entries, &$ids) {
            $e = $entry->entry();
            if (!$e) {
                return;
            }

            if ($e->published() === false) {
                return;
            }

            $ids[] = $e->id();
            $e->set('curated_collections', $entry->toArray());
            $e->set('curated_collection_source', 'list');
            $entries[] = $e;
        });

        if ($fallback && count($entries) < $limit) {
            $fallbackEntries = Entry::query()
                ->where('collection', $curatedCollection->fallback_collection)
                ->whereNotIn('id', $ids)
                ->limit($limit - count($entries))
                ->orderBy($curatedCollection->fallback_sort_field, $curatedCollection->fallback_sort_direction)
                ->get();
            $fallbackEntries->transform(function ($e) {
                $e->set('curated_collection_source', 'fallback');
                return $e;
            });
            $entries = array_merge($entries, $fallbackEntries->all());
        }

        return $entries;
    }
}
