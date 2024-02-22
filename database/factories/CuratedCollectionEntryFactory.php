<?php

namespace Database\Factories\Tv2regionerne\StatamicCuratedCollection\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tv2regionerne\StatamicCuratedCollection\Models\CuratedCollectionEntry;

class CuratedCollectionEntryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CuratedCollectionEntry::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'collection' => 'articles',
            'site' => 'default',
            'data' => [],
            'status' => 'published',
        ];
    }
}
