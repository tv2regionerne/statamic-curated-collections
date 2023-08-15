<?php

namespace Tv2regionerne\StatamicCuratedCollection\Facades;

use Illuminate\Support\Facades\Facade;
use Tv2regionerne\StatamicCuratedCollection\Repositories\CuratedCollectionRepository;

class CuratedCollection extends Facade
{
    protected static function getFacadeAccessor()
    {
        if (config('statamic.curated-collections.driver') === 'eloquent') {
            return \Tv2regionerne\StatamicCuratedCollection\Eloquent\Repositories\CuratedCollectionRepository::class;
        }
        return CuratedCollectionRepository::class;
    }
}
