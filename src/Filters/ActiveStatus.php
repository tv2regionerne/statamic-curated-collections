<?php

namespace Tv2regionerne\StatamicCuratedCollection\Filters;

use Statamic\Query\Scopes\Filter;

class ActiveStatus extends Filter
{
    public function visibleTo($key)
    {
        return false;
    }

    public function apply($query, $values)
    {
        $query->where('published', $values['status'] === 'published');
    }
}
