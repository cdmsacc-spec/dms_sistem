<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\CrewKontrak;
use Illuminate\Auth\Access\HandlesAuthorization;

class CrewKontrakPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view-any:crew-kontrak');
    }

    public function view(AuthUser $authUser, CrewKontrak $crewKontrak): bool
    {
        return $authUser->can('view:crew-kontrak');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create:crew-kontrak');
    }

    public function update(AuthUser $authUser, CrewKontrak $crewKontrak): bool
    {
        return $authUser->can('update:crew-kontrak');
    }

    public function delete(AuthUser $authUser, CrewKontrak $crewKontrak): bool
    {
        return $authUser->can('delete:crew-kontrak');
    }

    public function restore(AuthUser $authUser, CrewKontrak $crewKontrak): bool
    {
        return $authUser->can('restore:crew-kontrak');
    }

    public function forceDelete(AuthUser $authUser, CrewKontrak $crewKontrak): bool
    {
        return $authUser->can('force-delete:crew-kontrak');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force-delete-any:crew-kontrak');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore-any:crew-kontrak');
    }

    public function replicate(AuthUser $authUser, CrewKontrak $crewKontrak): bool
    {
        return $authUser->can('replicate:crew-kontrak');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder:crew-kontrak');
    }

}