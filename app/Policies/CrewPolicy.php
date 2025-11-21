<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Crew;
use Illuminate\Auth\Access\HandlesAuthorization;

class CrewPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view-any:crew');
    }

    public function view(AuthUser $authUser, Crew $crew): bool
    {
        return $authUser->can('view:crew');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create:crew');
    }

    public function update(AuthUser $authUser, Crew $crew): bool
    {
        return $authUser->can('update:crew');
    }

    public function delete(AuthUser $authUser, Crew $crew): bool
    {
        return $authUser->can('delete:crew');
    }

    public function restore(AuthUser $authUser, Crew $crew): bool
    {
        return $authUser->can('restore:crew');
    }

    public function forceDelete(AuthUser $authUser, Crew $crew): bool
    {
        return $authUser->can('force-delete:crew');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force-delete-any:crew');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore-any:crew');
    }

    public function replicate(AuthUser $authUser, Crew $crew): bool
    {
        return $authUser->can('replicate:crew');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder:crew');
    }

}