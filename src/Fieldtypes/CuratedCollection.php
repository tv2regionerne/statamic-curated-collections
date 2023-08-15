<?php

namespace Tv2regionerne\StatamicCuratedCollection\Fieldtypes;

use Illuminate\Support\Collection;
use Statamic\Entries\Entry;
use Statamic\Fields\Fieldtype;
use Tv2regionerne\StatamicCuratedCollection\Models\CuratedCollectionEntry;

class CuratedCollection extends Fieldtype
{
    /**
     * The blank/default value.
     *
     * @return array
     */
    public function defaultValue()
    {
        return null;
    }

    /**
     * Pre-process the data before it gets sent to the publish page.
     *
     * @param mixed $data
     * @return array|mixed
     */
    public function preProcess($data)
    {
        return $data;
    }

    /**
     * Process the data before it gets saved.
     *
     * @param mixed $data
     * @return array|mixed
     */
    public function process($data)
    {
        return $data;
    }
}
