<?php

namespace Tv2regionerne\StatamicCuratedCollection\Tags;

use Statamic\Facades\Entry;
use Statamic\Tags\Tags;
use Tv2regionerne\StatamicCuratedCollection\Events\CuratedCollectionTagEvent;
use Tv2regionerne\StatamicCuratedCollection\Models\CuratedCollection;
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
        if (! $curatedCollection) {
            return null;
        }

        $fallback = $this->params->get('fallback', false);
        $limit = $this->params->get('limit', 10);
        $offset = $this->params->get('offset', 0);

        $entries = [];
        $query = CuratedCollectionEntry::query()
            ->where('curated_collection_id', $curatedCollection->id)
            ->where('status', 'published')
            ->ordered()
            ->limit($limit);

        if ($ids = $this->deduplicateApply()) {
            $query->whereNotIn('entry_id', $ids);
        }

        $query->get()->each(function (CuratedCollectionEntry $entry) use (&$entries, &$ids) {
            $e = $entry->entry();
            if (! $e) {
                return;
            }

            if ($e->published() === false) {
                return;
            }

            $ids[] = $e->id();
            $e->set('curated_collection_data', $entry->processedData());
            $e->set('curated_collection_order', $entry->order_column);
            $e->set('curated_collection_source', 'list');
            $entries[] = $e;
        });

        if ($fallback && count($entries) < $limit) {
            $fallbackEntries = Entry::query()
                ->where('collection', $curatedCollection->fallback_collection)
                ->where('status', 'published')
                ->whereNotIn('id', $ids ?? [])
                ->limit($limit - count($entries))
                ->orderBy($curatedCollection->fallback_sort_field, $curatedCollection->fallback_sort_direction)
                ->get();
            $fallbackEntries->transform(function ($e) {
                $e->set('curated_collection_source', 'fallback');

                return $e;
            });
            $entries = array_merge($entries, $fallbackEntries->all());
        }

        if ($offset > 0) {
            $entries = collect($entries)->skip($offset)->all();
        }

        if ($as = $this->params->get('as')) {
            return [$as => $entries];
        }

        if (count($entries) > 0) {
            $this->deduplicateUpdate($entries);
        }

        CuratedCollectionTagEvent::dispatch($tag);

        return $entries;
    }

    protected function deduplicateApply()
    {
        if (! $this->params->get('deduplicate', false)) {
            return;
        }

        return app('deduplicate')->fetch();
    }

    protected function deduplicateUpdate($entries)
    {
        if (! $this->params->get('deduplicate', false)) {
            return;
        }

        if ($as = $this->params->get('as')) {
            $entries = $entries[$as];
        }

        $ids = collect($entries)->pluck('id')->all();

        app('deduplicate')->merge($ids);
    }
}
