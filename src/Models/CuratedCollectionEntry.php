<?php

namespace Tv2regionerne\StatamicCuratedCollection\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Spatie\EloquentSortable\SortableTrait;
use Statamic\Facades\Entry;

class CuratedCollectionEntry extends Model
{
    use HasUuids;
    use SortableTrait;

    public $sortable = [
        'order_column_name' => 'order_column',
        'sort_when_creating' => false,
    ];

    protected $fillable = [
        'title',
        'collection',
        'site',
        'entry_id',
        'data',
        'order_column',
        'publish_order',
        'expiration_time',
        'status',
        'unpublish_at',
    ];

    protected $casts = [
        'data' => 'object',
    ];

    public function curatedCollection(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CuratedCollection::class);
    }

    public function entry($entry = null) {
        if (!$entry) {
            return Entry::find($this->entry_id);
        }
        $this->entry_id = $entry->id();
        return $this;
    }

    public function collection($collection = null) {
        if (!$collection) {
            return $this->collection;
        }
        $this->collection = $collection;
        return $this;
    }

    public function status($status = null) {
        if (!$status) {
            return $this->status;
        }
        $this->status = $status;
        return $this;
    }

    public function data($data = null) {
        if (!$data) {
            return $this->data;
        }
        $this->data = $data;
        return $this;
    }

    /**
     * Proccess the data with the blueprint and return
     *
     * @return array
     */
    public function processedData() {
        $blueprint = $this->curatedCollection->blueprint();
        return $blueprint->fields()->addValues(json_decode(json_encode($this->data), true))->augment()->values();
    }

    public function publishOrder($publishOrder = null) {
        if (!$publishOrder) {
            return $this->publish_order;
        }
        $this->publish_order = $publishOrder;
        return $this;
    }

    public function expirationTime($expirationTime = null) {
        if (!$expirationTime) {
            return $this->expiration_time;
        }
        $this->expiration_time = $expirationTime;
        return $this;
    }

    public function unpublishAt($unpublishAt = null) {
        if (!$unpublishAt) {
            return $this->unpublish_at;
        }
        // Handle the statamic date/time array
        if (is_array($unpublishAt)) {
            $this->unpublish_at = Carbon::make($unpublishAt['date'] .' '. $unpublishAt['time']);
            return $this;
        }
        $this->unpublish_at = $unpublishAt;
        return $this;
    }

    public function publish(): self
    {
        $this->setPosition($this->publish_order);

        $update = ['status' => 'published'];
        if ($this->curatedCollection->update_expiration_on_publish) {
            $update['expiration'] = now()->addHours($this->expiration_time ?? $this->curatedCollection->expiration_time);
        }

        $this->update($update);
        return $this;
    }

    public function setPosition(?int $position = null) {
        // Get id's of published entries
        $ids = $this
            ->buildSortQuery()
            ->published()
            ->ordered()
            ->select('id')
            ->pluck('id')
            ->all();

        // If order is empty (`0`/`NULL`) place the article in the end of the list.
        $orderColumn = $position ?? $this->getHighestOrderNumber() + 1;
        // Remove the article's ID from the list and re-insert it at the desired location.
        $order = array_diff($ids, [$this->id]);
        array_splice($order, max(0, $orderColumn - 1), 0, $this->id);
        // Update articles with new order.
        CuratedCollectionEntry::setNewOrder($order);
    }

    public function scopePublished(Builder $query): void {
        $query->where('status', 'published');
    }

    public function scopeDraft(Builder $query): void {
        $query->where('status', 'draft');
    }

    public function buildSortQuery()
    {
        return static::query()->where('curated_collection_id', $this->curated_collection_id);
    }
}
