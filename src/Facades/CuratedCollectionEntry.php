<?php

namespace Tv2regionerne\StatamicCuratedCollection\Facades;

use Illuminate\Support\Facades\Facade;
use Tv2regionerne\StatamicCuratedCollection\Repositories\CuratedCollectionEntryRepository;

class CuratedCollectionEntry extends Facade
{
    protected static function getFacadeAccessor()
    {
        return CuratedCollectionEntryRepository::class;
    }
}
