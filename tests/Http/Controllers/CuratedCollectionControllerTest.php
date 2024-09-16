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

    $role = Role::make()->handle('test')->permissions(['access cp']);
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

    $role = Role::make()->handle('test')->permissions(['access cp']);
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

    $role = Role::make()->handle('test')->permissions(['access cp']);
    $role->save();

    $user = User::make()->save();
    $user->assignRole($role);

    $this
        ->actingAs($user)
        ->get(cp_route('curated-collections.edit', [$curated->handle]))
        ->assertRedirect();
});

test('gets curated collection index page if the user can view a curated collection', function () {
    $curated = CuratedCollection::factory()->make();
    $curated->title = 'Test Curated';
    $curated->handle = 'test';
    $curated->fallback_collection = 'articles';
    $curated->save();

    $curatedTwo = CuratedCollection::factory()->make();
    $curatedTwo->title = 'Test Curated Two';
    $curatedTwo->handle = 'test_two';
    $curatedTwo->fallback_collection = 'articles';
    $curatedTwo->save();

    $role = Role::make()->handle('test')->permissions(['access cp', "view curated-collection {$curated->handle} entries"]);
    $role->save();

    $user = User::make()->save();
    $user->assignRole($role);

    $this
        ->actingAs($user)
        ->get(cp_route('curated-collections.index'))
        ->assertOk()
        ->assertViewIs('statamic-curated-collections::curated-collections.index')
        ->assertSee([
            'curated-collection-listing',
        ])
        ->assertSeeText('Test Curated')
        ->assertDontSeeText('Test Curated Two');
});

test('gets curated collection show page if the user has view permission for that collection', function () {
    $curated = CuratedCollection::factory()->make();
    $curated->handle = 'test';
    $curated->fallback_collection = 'articles';
    $curated->save();

    $role = Role::make()->handle('test')->permissions(['access cp', "view curated-collection {$curated->handle} entries"]);
    $role->save();

    $user = User::make()->save();
    $user->assignRole($role);

    $this
        ->actingAs($user)
        ->get(cp_route('curated-collections.show', [$curated->handle]))
        ->assertOk()
        ->assertViewIs('statamic-curated-collections::curated-collections.show')
        ->assertSee([
            'curated-collection-view',
        ]);
});

test('doesn\'t get curated collection show page if the user has no view permission for that collection', function () {
    $curated = CuratedCollection::factory()->make();
    $curated->handle = 'test';
    $curated->fallback_collection = 'articles';
    $curated->save();

    $role = Role::make()->handle('test')->permissions(['access cp', 'view curated-collection some_other_collection entries']);
    $role->save();

    $user = User::make()->save();
    $user->assignRole($role);

    $this
        ->actingAs($user)
        ->get(cp_route('curated-collections.show', [$curated->handle]))
        ->assertRedirect();
});

test('gets curated collection edit page when user has general edit permissions', function () {
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
        ->get(cp_route('curated-collections.edit', [$curated->handle]))
        ->assertOk()
        ->assertViewIs('statamic-curated-collections::curated-collections.edit')
        ->assertSee([
            'curated-collection-edit-form',
        ]);
});

test('gets curated collection edit page when user has collection specific edit permissions', function () {
    $curated = CuratedCollection::factory()->make();
    $curated->handle = 'test';
    $curated->fallback_collection = 'articles';
    $curated->save();

    $role = Role::make()->handle('test')->permissions(['access cp', "edit curated-collection {$curated->handle}"]);
    $role->save();

    $user = User::make()->save();
    $user->assignRole($role);

    $this
        ->actingAs($user)
        ->get(cp_route('curated-collections.edit', [$curated->handle]))
        ->assertOk()
        ->assertViewIs('statamic-curated-collections::curated-collections.edit')
        ->assertSee([
            'curated-collection-edit-form',
        ]);
});

test('gets curated collection create page when user has general create permissions', function () {
    $curated = CuratedCollection::factory()->make();
    $curated->handle = 'test';
    $curated->fallback_collection = 'articles';
    $curated->save();

    $role = Role::make()->handle('test')->permissions(['access cp', 'create curated-collections']);
    $role->save();

    $user = User::make()->save();
    $user->assignRole($role);

    $this
        ->actingAs($user)
        ->get(cp_route('curated-collections.create', [$curated->handle]))
        ->assertOk()
        ->assertViewIs('statamic-curated-collections::curated-collections.create')
        ->assertSee([
            'curated-collection-create-form',
        ]);
});
