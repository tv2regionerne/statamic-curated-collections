<?php

namespace Tv2regionerne\StatamicCuratedCollection\Data;

use Illuminate\Support\Carbon;
use Statamic\Data\AbstractAugmented;
use Statamic\Entries\Entry;
use Statamic\Facades\Collection;
use Statamic\Statamic;

class AugmentedEntry extends AbstractAugmented
{
    public function keys()
    {
        return collect($this->commonKeys())
            ->merge($this->blueprintFields()->keys())
            ->unique()->sort()->values()->all();
    }

    private function commonKeys()
    {
        return [
            'id',
            'entry',
            'collection',
            'curated_collection',
            'order',
            'unpublish_at',
        ];
    }

    protected function entry()
    {
        $entry = Entry::find($this->data->entry());
        return [$entry?->toArray() ?? null];
    }

    protected function unpublishAt()
    {
        return Carbon::make($this->data->unpublishAt())->format('Y-m-d H:i:s');
    }


    public function date()
    {
        return $this->data->collection()->dated()
            ? $this->data->date()
            : $this->wrapValue($this->getFromData('date'), 'date');
    }
}
