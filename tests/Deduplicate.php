<?php

namespace Tv2regionerne\StatamicCuratedCollection\Tests;

class Deduplicate
{
    protected $ids;

    public function __construct()
    {
        $this->ids = collect();
    }

    public function fetch()
    {
        return $this->ids->all();
    }

    public function merge($ids)
    {
        $this->ids = $this->ids->concat($ids);

        return $this;
    }
}
