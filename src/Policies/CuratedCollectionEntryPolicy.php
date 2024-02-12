<?php

namespace Tv2regionerne\StatamicCuratedCollection\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Tv2regionerne\StatamicCuratedCollection\Models\CuratedCollectionEntry;

class CuratedCollectionEntryPolicy
{
    use HandlesAuthorization;

    public function before($user, $ability)
    {
        $user = \Statamic\Facades\User::fromUser($user);

        if ($user->hasPermission('manage curated-collections')) {
            return true;
        }
    }

    /**
     * Determine whether the user can view any models.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny($user)
    {
        $user = \Statamic\Facades\User::fromUser($user);

        if ($user->hasPermission('view all curated-collection entries')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view($user, CuratedCollectionEntry $curatedCollectionEntry)
    {
        $user = \Statamic\Facades\User::fromUser($user);

        if ($user->hasPermission('view all curated-collection entries') || $user->hasPermission("view curated-collection {$curatedCollectionEntry->handle} entries")) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create($user, CuratedCollectionEntry $curatedCollectionEntry)
    {
        $user = \Statamic\Facades\User::fromUser($user);

        if ($user->hasPermission('create all curated-collections entries') || $user->hasPermission("create curated-collection {$curatedCollectionEntry->handle} entries")) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update($user, CuratedCollectionEntry $curatedCollectionEntry)
    {
        $user = \Statamic\Facades\User::fromUser($user);

        if ($user->hasPermission('edit all curated-collections entries') || $user->hasPermission("edit curated-collection {$curatedCollectionEntry->handle} entries")) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete($user, CuratedCollectionEntry $curatedCollectionEntry)
    {
        $user = \Statamic\Facades\User::fromUser($user);

        if ($user->hasPermission('edit curated-collections')) {
            return true;
        }

        return false;
    }
}
