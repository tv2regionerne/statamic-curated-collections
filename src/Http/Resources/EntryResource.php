<?php

namespace Tv2regionerne\StatamicCuratedCollection\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class EntryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id(),
            'title' => $this->resource->value('title'),
            'full_title' => $this->resource->value('full_title'),
            'permalink' => $this->resource->absoluteUrl(),
            'published' => $this->resource->published(),
            'status' => $this->resource->status(),
            'private' => $this->resource->private(),
            'edit_url' => $this->resource->editUrl(),
            'template' => $this->resource->template,
            'template_abbr' => $this->templateAbbr($this->resource),
            'collection' => [
                'title' => $this->resource->collection()->title(),
                'handle' => $this->resource->collection()->handle(),
            ],
        ];
    }

    protected function templateAbbr($entry)
    {
        if (! $entry->template) {
            return null;
        }
        $words = Str::of($entry->template)->afterLast('/')->explode('_');
        $abbr = $words->count() > 1
            ? $words->slice(0, 2)->map(fn ($word) => Str::substr($word, 0, 1))->join('')
            : Str::substr($words->first(), 0, 2);
        return Str::upper($abbr);
    }
}
