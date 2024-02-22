<?php

use Statamic\Facades\Role;
use Statamic\Facades\User;
use Tv2regionerne\StatamicCuratedCollection\Models\CuratedCollection;

test('gets curated collection index', function () {
    $curated = CuratedCollection::factory()->make();
    $curated->handle = 'test';
    $curated->fallback_collection = 'articles';
    $curated->save();

    $user = User::make()->makeSuper()->save();

    $this
        ->actingAs($user)
        ->get(cp_route('curated-collections.index'))
        ->assertOk()
        ->assertViewIs('statamic-curated-collections::curated-collections.index')
        ->assertSee([
            'curated-collection-listing',
        ]);
});

test('doesn\'t get curated collection index when the user has no permissions', function () {
    $curated = CuratedCollection::factory()->make();
    $curated->handle = 'test';
    $curated->fallback_collection = 'articles';
    $curated->save();

    $role = Role::make()->handle('test')->permissions(['access_cp']);
    $role->save();

    $user = User::make()->save();
    $user->assignRole($role);

    $this
        ->actingAs($user)
        ->get(cp_route('curated-collections.index'))
        ->assertRedirect();
});

test('gets curated collection show page', function () {
    $curated = CuratedCollection::factory()->make();
    $curated->handle = 'test';
    $curated->fallback_collection = 'articles';
    $curated->save();

    $user = User::make()->makeSuper()->save();

    $this
        ->actingAs($user)
        ->get(cp_route('curated-collections.show', [$curated->handle]))
        ->assertOk()
        ->assertViewIs('statamic-curated-collections::curated-collections.show')
        ->assertSee([
            'curated-collection-view',
        ]);
});

test('doesn\'t get gets curated collection show page when the user has no permissions', function () {
    $curated = CuratedCollection::factory()->make();
    $curated->handle = 'test';
    $curated->fallback_collection = 'articles';
    $curated->save();

    $role = Role::make()->handle('test')->permissions(['access_cp']);
    $role->save();

    $user = User::make()->save();
    $user->assignRole($role);

    $this
        ->actingAs($user)
        ->get(cp_route('curated-collections.show', [$curated->handle]))
        ->assertRedirect();
});

test('gets curated collection edit page', function () {
    $curated = CuratedCollection::factory()->make();
    $curated->handle = 'test';
    $curated->fallback_collection = 'articles';
    $curated->save();

    $user = User::make()->makeSuper()->save();

    $this
        ->actingAs($user)
        ->get(cp_route('curated-collections.edit', [$curated->handle]))
        ->assertOk()
        ->assertViewIs('statamic-curated-collections::curated-collections.edit')
        ->assertSee([
            'curated-collection-edit-form',
        ]);
});

test('doesn\'t get gets curated collection edit page when the user has no permissions', function () {
    $curated = CuratedCollection::factory()->make();
    $curated->handle = 'test';
    $curated->fallback_collection = 'articles';
    $curated->save();

    $role = Role::make()->handle('test')->permissions(['access_cp']);
    $role->save();

    $user = User::make()->save();
    $user->assignRole($role);

    $this
        ->actingAs($user)
        ->get(cp_route('curated-collections.edit', [$curated->handle]))
        ->assertRedirect();
});

