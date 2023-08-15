<?php

namespace Tv2regionerne\StatamicCuratedCollection\Policies;

use App\Models\User;
use Tv2regionerne\StatamicCuratedCollection\Models\CuratedCollection;
use Illuminate\Auth\Access\HandlesAuthorization;

class CuratedCollectionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  CuratedCollection  $curatedCollection
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, CuratedCollection $curatedCollection)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  CuratedCollection  $curatedCollection
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, CuratedCollection $curatedCollection)
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  CuratedCollection  $curatedCollection
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, CuratedCollection $curatedCollection)
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  CuratedCollection  $curatedCollection
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, CuratedCollection $curatedCollection)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  CuratedCollection  $curatedCollection
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, CuratedCollection $curatedCollection)
    {
        //
    }
}
