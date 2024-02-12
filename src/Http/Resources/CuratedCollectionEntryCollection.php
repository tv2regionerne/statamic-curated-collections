<?php

namespace Tv2regionerne\StatamicCuratedCollection\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CuratedCollectionEntryCollection extends ResourceCollection
{
    protected $blueprint;

    public function blueprint($blueprint)
    {
        $this->blueprint = $blueprint;

        return $this;
    }

    public function with($request)
    {
        return [
            'meta' => [
                'columns' => $this->columns()->values(),
            ],
        ];
    }

    protected function columns()
    {
        return $this->blueprint->columns();
    }
}
