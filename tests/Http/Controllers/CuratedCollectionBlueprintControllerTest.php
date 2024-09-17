<?php

use Statamic\Facades\Role;
use Statamic\Facades\User;
use Tv2regionerne\StatamicCuratedCollection\Models\CuratedCollection;

test('can edit blueprints when the user has general edit permissions', function () {
    $curated = CuratedCollection::factory()->make();
    $curated->handle = 'test';
    $curated->fallback_collection = 'articles';
    $curated->save();

    $role = Role::make()->handle('test')->permissions(['access cp', 'edit curated-collections']);
    $role->save();

    $user = User::make()->save();
    $user->assignRole($role);

    $this
        ->actingAs($user)
        ->get(cp_route('curated-collections.blueprint.edit', [$curated->handle]))
        ->assertOk()
        ->assertViewIs('statamic-curated-collections::curated-collections.blueprints.edit')
        ->assertSee([
            'blueprint-builder',
        ]);
});

test('can edit blueprints when the user has collection edit permissions', function () {
    $curated = CuratedCollection::factory()->make();
    $curated->handle = 'test';
    $curated->fallback_collection = 'articles';
    $curated->save();

    $role = Role::make()->handle('test')->permissions(['access cp', 'edit curated-collection test']);
    $role->save();

    $user = User::make()->save();
    $user->assignRole($role);

    $this
        ->actingAs($user)
        ->get(cp_route('curated-collections.blueprint.edit', [$curated->handle]))
        ->assertOk()
        ->assertViewIs('statamic-curated-collections::curated-collections.blueprints.edit')
        ->assertSee([
            'blueprint-builder',
        ]);
});

test('can edit blueprints when the user is a super', function () {
    $curated = CuratedCollection::factory()->make();
    $curated->handle = 'test';
    $curated->fallback_collection = 'articles';
    $curated->save();

    $user = User::make()->makeSuper()->save();

    $this
        ->actingAs($user)
        ->get(cp_route('curated-collections.blueprint.edit', [$curated->handle]))
        ->assertOk()
        ->assertViewIs('statamic-curated-collections::curated-collections.blueprints.edit')
        ->assertSee([
            'blueprint-builder',
        ]);
});

test('doesn\'t get gets curated collection blueprint edit page when the user has no permissions', function () {
    $curated = CuratedCollection::factory()->make();
    $curated->handle = 'test';
    $curated->fallback_collection = 'articles';
    $curated->save();

    $role = Role::make()->handle('test')->permissions(['access cp']);
    $role->save();

    $user = User::make()->save();
    $user->assignRole($role);

    $this
        ->actingAs($user)
        ->get(cp_route('curated-collections.blueprint.edit', [$curated->handle]))
        ->assertRedirect();
});

test('doesn\'t get gets curated collection blueprint edit page when the user can edit other curated collection blueprints', function () {
    $curated = CuratedCollection::factory()->make();
    $curated->handle = 'test';
    $curated->fallback_collection = 'articles';
    $curated->save();

    $role = Role::make()->handle('test')->permissions(['access cp', 'edit curated-collection not_test']);
    $role->save();

    $user = User::make()->save();
    $user->assignRole($role);

    $this
        ->actingAs($user)
        ->get(cp_route('curated-collections.blueprint.edit', [$curated->handle]))
        ->assertRedirect();
});
