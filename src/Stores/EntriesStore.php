<?php

namespace Tv2regionerne\StatamicCuratedCollection\Stores;

use Statamic\Stache\Stores\AggregateStore;
use Tv2regionerne\StatamicCuratedCollection\Facades\CuratedCollection;

class EntriesStore extends AggregateStore
{

    protected $childStore = CollectionEntriesStore::class;

    public function key()
    {
        return 'curated-collection-entries';
    }

    public function discoverStores()
    {
        return CuratedCollection::handles()->map(function ($handle) {
            return $this->store($handle);
        });
    }
}
