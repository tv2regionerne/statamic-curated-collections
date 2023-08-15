<?php

namespace Tv2regionerne\StatamicCuratedCollection\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Statamic\Events\NavBlueprintFound;
use Statamic\Facades\Blueprint;

class CuratedCollection extends Model
{

    use HasUuids;

    protected $appends = ['display_form_computed'];

    protected $fillable = [
        'title',
        'handle',
        'site',
        'collections',
        'max_items',
        'display_form',
        'fallback_collection',
        'fallback_sort_field',
        'fallback_sort_direction',
        'automation',
        'update_expiration_on_publish',
        'expiration_time',
    ];

    protected $casts = [
        'automation' => 'boolean',
        'update_expiration_on_publish' => 'boolean',
        'display_form' => 'boolean',
        'collections' => 'array',
        'settings' => 'json',
    ];

    public function entries(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CuratedCollectionEntry::class);
    }

    public function addEntryBlueprint()
    {
        $blueprint = $this->blueprint();
        if (!$blueprint->hasField('entry'))
        {
            $blueprint->ensureField('id', [
                'visibility' => 'hidden',
                'type' => 'text',
            ], null, true);
            $blueprint->ensureField('curated_collection_id', [
                'visibility' => 'hidden',
                'type' => 'text',
            ], null, true);
            $blueprint->ensureField('collection', [
                'visibility' => 'hidden',
                'type' => 'collections',
            ], null, true);
            $blueprint->ensureField('unpublish_at', [
                'type' => 'date',
                'display' => __('Fjern fra listen den'),
                'instructions' => __('Indlægget fjernes automatisk fra listen på denne dato'),
                'time_enabled' => true,
                'format' => 'c',
                'width' => 50,
                'validate' => [
                    'nullable',
                ]
            ], null, true);
            $blueprint->ensureField('published', [
                'visibility' => 'hidden',
                'type' => 'toggle',
                'display' => __('Published'),
                //'instructions' => __(''),
                'width' => 50,
            ], null, true);
            $blueprint->ensureField('expiration_time', [
                'type' => 'integer',
                'display' => __('Udløbstid i timer'),
                'instructions' => __('Overskriv standard indstillingerne for Antal timer for hvornår denne entry skal fjernes fra listen. Datoen for fjernelse sættes automatisk ved publisering.'),
                'width' => 50,
                'if' => [
                    'published' => false,
                ],
            ], null, true);
            $blueprint->ensureField('order', [
                'type' => 'integer',
                'display' => __('Position'),
                'instructions' => __('Tilføjes i bunden hvis der ikke sættes en position'),
                'width' => 50,
                'if' => [
                    'published' => true,
                ],
            ], null, true);
            $blueprint->ensureField('publish_order', [
                'type' => 'integer',
                'display' => __('Publish Position'),
                'instructions' => __('Tilføjes i bunden hvis der ikke sættes en position'),
                'width' => 50,
                'if' => [
                    'published' => false,
                ],
            ], null, true);
            $blueprint->ensureField('entry', [
                'visibility' => 'hidden',
                'type' => 'entries',
                'display' => __('Entry'),
                'mode' => 'default',
                'collections' => $this->collections,
                'max_items' => 1,
                'create' => false,
                'validate' => [
                    'required',
                ]
            ], null, true);

        }

        return $blueprint;
    }

    public function blueprint()
    {
        $blueprint = Blueprint::find('curated-collection.'.$this->handle)
            ?? Blueprint::makeFromFields([])->setHandle($this->handle)->setNamespace('curated-collection');

        NavBlueprintFound::dispatch($blueprint, $this);

        return $blueprint;
    }

    public static function findByHandle($handle): CuratedCollection|Model|null
    {
        return self::query()->where('handle', $handle)->first();
    }

    public function reorderEntries() {
        // Get id's of published entries
        $ids = $this
            ->entries()
            ->published()
            ->ordered()
            ->select('id')
            ->pluck('id')
            ->all();

        // Update articles with new order.
        CuratedCollectionEntry::setNewOrder($ids);
    }

    public function getDisplayFormComputedAttribute()
    {
        $blueprint = $this->addEntryBlueprint();
        $requiredFields = $blueprint->fields()->all()
            ->except(['entry', 'order'])
            ->where(fn ($field) => $field->isRequired());
        return $requiredFields->count() || $this->display_form;
    }

    public function showUrl()
    {
        return cp_route('curated-collections.show', $this->handle);
    }

    public function editUrl()
    {
        return cp_route('curated-collections.edit', $this->handle);
    }

    public function deleteUrl()
    {
        return cp_route('curated-collections.destroy', $this->handle);
    }

    public function blueprintUrl()
    {
        return cp_route('curated-collections.blueprint.edit', $this->handle);
    }
}
