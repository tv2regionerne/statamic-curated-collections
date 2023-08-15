<?php

namespace Tv2regionerne\StatamicCuratedCollection\Stores;

use Statamic\Facades\Site;
use Statamic\Facades\YAML;
use Statamic\Stache\Stores\ChildStore;
use Statamic\Support\Str;
use Tv2regionerne\StatamicCuratedCollection\Facades\CuratedCollectionEntry;

class CollectionEntriesStore extends ChildStore
{

    public function makeItemFromFile($path, $contents)
    {
        [$curatedCollection, $site] = $this->extractAttributesFromPath($path);
        $data = YAML::file($path)->parse($contents);

        if (! $id = array_pull($data, 'id')) {
            $idGenerated = true;
            $id = app('stache')->generateId();
        }

        $entry = CuratedCollectionEntry::make()
            ->id($id)
            ->order(array_pull($data, 'order', 0))
            ->curatedCollection($curatedCollection)
            ->fill($data);

        if (isset($idGenerated)) {
            $entry->save();
        }

        return $entry;
    }

    protected function extractAttributesFromPath($path)
    {
        $site = Site::default()->handle();
        $collection = pathinfo($path, PATHINFO_DIRNAME);
        $collection = str_after($collection, $this->parent->directory());

        if (Site::hasMultiple()) {
            [$collection, $site] = explode('/', $collection);
        }

        // Support entries within subdirectories at any level.
        if (Str::contains($collection, '/')) {
            $collection = str_before($collection, '/');
        }

        return [$collection, $site];
    }
}
