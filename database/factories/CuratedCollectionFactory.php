<?php

namespace Database\Factories\Tv2regionerne\StatamicCuratedCollection\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tv2regionerne\StatamicCuratedCollection\Models\CuratedCollection;

class CuratedCollectionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CuratedCollection::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->name(),
            'handle' => $this->faker->name(),
            'site' => 'default',
            'collections' => ['articles'],
            'display_form' => false,
            'fallback_collection' => 'articles',
            'fallback_sort_field' => 'title',
            'fallback_sort_direction' => 'asc',
            'automation' => true,
            'update_expiration_on_publish' => true,
            'expiration_time' => now()->addDays(7),
        ];
    }
}
