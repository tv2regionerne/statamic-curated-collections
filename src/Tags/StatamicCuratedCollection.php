<?php

namespace Tv2regionerne\StatamicCuratedCollection\Tags;

use Illuminate\Contracts\Pagination\Paginator;
use Statamic\Data\DataCollection;
use Statamic\Facades\Entry;
use Statamic\Tags\Concerns\GetsQueryResults;
use Statamic\Tags\Concerns\OutputsItems;
use Statamic\Tags\Tags;
use Tv2regionerne\StatamicCuratedCollection\Events\CuratedCollectionTagEvent;
use Tv2regionerne\StatamicCuratedCollection\Models\CuratedCollection;
use Tv2regionerne\StatamicCuratedCollection\Models\CuratedCollectionEntry;

class StatamicCuratedCollection extends Tags
{
    use GetsQueryResults, OutputsItems;

    protected static $handle = 'curated_collection';

    /**
     * The {{ curated_collection from="x" }} tag.
     *
     * @return array
     */
    public function index()
    {
        if ($from = $this->params->get('from')) {
            return $this->wildcard($from);
        }
    }

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
            ->ordered();

        if ($ids = $this->deduplicateApply()) {
            $query->whereNotIn('entry_id', $ids);
        }
        if ($ids = $this->params->get('id:not_in')) {
            if (is_string($ids)) {
                $ids = explode('|', $ids);
            }
            $query->whereNotIn('entry_id', $ids);
        }

        $results = $this->results($query);

        $entries = ($results instanceof Paginator ? $results->getCollection() : $results)
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
                    'curated_collection_source' => 'list',
                ]);

                return $e;
            })
            ->filter();

        if (! $results instanceof Paginator) {
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
        }

        if ($results instanceof Paginator) {
            $results->setCollection(new DataCollection($entries));
        } else {
            $results = $entries;
        }

        $this->deduplicateUpdate($entries);

        CuratedCollectionTagEvent::dispatch($tag);

        return $this->output($results);
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

    protected function getPaginationData($paginator)
    {
        return [
            'total_items' => $paginator->total(),
            'items_per_page' => $paginator->perPage(),
            'total_pages' => $paginator->lastPage(),
            'current_page' => $paginator->currentPage(),
            'prev_page' => $paginator->previousPageUrl(),
            'next_page' => $paginator->nextPageUrl(),
            'auto_links' => (string) $paginator->render('pagination::default'),
        ];
    }
}
