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

        $limit = $this->params->get('limit', 10);

        $entries = [];

        $query = CuratedCollectionEntry::query()
            ->where('curated_collection_id', $curatedCollection->id)
            ->where('status', 'published')
            ->ordered()
            ->limit($limit);

        if ($ids = $this->deduplicateApply()) {
            $query->whereNotIn('entry_id', $ids);
        }

        $entries = $query->get()
            ->map(function (CuratedCollectionEntry $entry) use (&$ids) {
                if (! $e = $entry->entry()) {
                    return;
                }

                if ($e->published() === false) {
                    return;
                }

                $ids[] = $e->id();

                $e->merge([
                    'curated_collection_data' => $entry->processedData(),
                    'curated_collection_order' => $entry->order_column,
                    'curated_collection_source' => 'list'
                ]);

                return $e;
            })
            ->filter();

        if ($this->params->get('fallback', false) && count($entries) < $limit) {
            $fallbackEntries = Entry::query()
                ->where('collection', $curatedCollection->fallback_collection)
                ->where('status', 'published')
                ->whereNotIn('id', $ids ?? [])
                ->limit($limit - count($entries))
                ->orderBy($curatedCollection->fallback_sort_field, $curatedCollection->fallback_sort_direction)
                ->get()
                ->transform(function ($e) {
                    $e->set('curated_collection_source', 'fallback');

                    return $e;
                });

            $entries = $entries->concat($fallbackEntries);
        }

        $entries = $entries->all();

        $this->deduplicateUpdate($entries);

        CuratedCollectionTagEvent::dispatch($tag);

        if ($as = $this->params->get('as')) {
            return [$as => $entries];
        }

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

        $ids = collect($entries)->pluck('id')->all();

        app('deduplicate')->merge($ids);
    }
}
