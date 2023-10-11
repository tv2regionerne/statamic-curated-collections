<?php

namespace Tv2regionerne\StatamicCuratedCollection\Policies;

use App\Models\User;
use Tv2regionerne\StatamicCuratedCollection\Models\CuratedCollection;
use Illuminate\Auth\Access\HandlesAuthorization;

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
     * @param  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny($user)
    {
        $user = \Statamic\Facades\User::fromUser($user);

        return ! CuratedCollection::all()->filter(function ($curatedCollection) use ($user) {
            return $this->view($user, $curatedCollection);
        })->isEmpty();
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  $user
     * @param  CuratedCollection  $curatedCollection
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view($user, CuratedCollection $curatedCollection)
    {
        $user = \Statamic\Facades\User::fromUser($user);

        if ( $user->hasPermission("view curated-collection {$curatedCollection->handle} entries")) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create($user)
    {
        // handled by before()
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  $user
     * @param  CuratedCollection  $curatedCollection
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update($user, CuratedCollection $curatedCollection)
    {
        // handled by before()
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  $user
     * @param  CuratedCollection  $curatedCollection
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete($user, CuratedCollection $curatedCollection)
    {
        // handled by before()
    }

}
