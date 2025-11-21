<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Lookup;
use Illuminate\Auth\Access\HandlesAuthorization;

class LookupPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view-any:lookup');
    }

    public function view(AuthUser $authUser, Lookup $lookup): bool
    {
        return $authUser->can('view:lookup');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create:lookup');
    }

    public function update(AuthUser $authUser, Lookup $lookup): bool
    {
        return $authUser->can('update:lookup');
    }

    public function delete(AuthUser $authUser, Lookup $lookup): bool
    {
        return $authUser->can('delete:lookup');
    }

    public function restore(AuthUser $authUser, Lookup $lookup): bool
    {
        return $authUser->can('restore:lookup');
    }

    public function forceDelete(AuthUser $authUser, Lookup $lookup): bool
    {
        return $authUser->can('force-delete:lookup');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force-delete-any:lookup');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore-any:lookup');
    }

    public function replicate(AuthUser $authUser, Lookup $lookup): bool
    {
        return $authUser->can('replicate:lookup');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder:lookup');
    }

}