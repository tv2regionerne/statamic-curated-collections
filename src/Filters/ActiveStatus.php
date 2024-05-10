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
        if ($values['status'] === 'published') {
            $query->where('published', true);
        } else {
            $query->where('published', false);
        }
    }
}
