<?php

namespace Tv2regionerne\StatamicCuratedCollection\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Tv2regionerne\StatamicCuratedCollection\Models\CuratedCollection;

class CuratedCollectionPolicy
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

        if ($user->hasPermission('view curated-collections')) {
            return true;
        }

        return CuratedCollection::all()->first(function ($curatedCollection) use ($user) {
            return $this->view($user, $curatedCollection);
        }) ?? false;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view($user, CuratedCollection $curatedCollection)
    {
        $user = \Statamic\Facades\User::fromUser($user);

        if ($user->hasPermission('view curated-collections')) {
            return true;
        }

        if ($user->hasPermission("view curated-collection {$curatedCollection->handle} entries")) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create($user)
    {
        $user = \Statamic\Facades\User::fromUser($user);

        if ($user->hasPermission('create curated-collections')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update($user, CuratedCollection $curatedCollection)
    {
        $user = \Statamic\Facades\User::fromUser($user);

        if ($user->hasPermission('edit curated-collections')) {
            return true;
        }

        if ($user->hasPermission("edit curated-collection {$curatedCollection->handle}")) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete($user, CuratedCollection $curatedCollection)
    {
        $user = \Statamic\Facades\User::fromUser($user);

        if ($user->hasPermission('delete curated-collections')) {
            return true;
        }

        if ($user->hasPermission("delete curated-collection {$curatedCollection->handle}")) {
            return true;
        }

        return false;
    }
}
