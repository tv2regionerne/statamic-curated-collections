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
        $statuses = $values['status'] === 'published'
            ? ['published']
            : ['scheduled', 'expired', 'draft'];

        $query->whereIn('status', $statuses);
    }
}
