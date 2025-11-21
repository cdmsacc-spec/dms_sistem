<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Kapal;
use Illuminate\Auth\Access\HandlesAuthorization;

class KapalPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view-any:kapal');
    }

    public function view(AuthUser $authUser, Kapal $kapal): bool
    {
        return $authUser->can('view:kapal');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create:kapal');
    }

    public function update(AuthUser $authUser, Kapal $kapal): bool
    {
        return $authUser->can('update:kapal');
    }

    public function delete(AuthUser $authUser, Kapal $kapal): bool
    {
        return $authUser->can('delete:kapal');
    }

    public function restore(AuthUser $authUser, Kapal $kapal): bool
    {
        return $authUser->can('restore:kapal');
    }

    public function forceDelete(AuthUser $authUser, Kapal $kapal): bool
    {
        return $authUser->can('force-delete:kapal');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force-delete-any:kapal');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore-any:kapal');
    }

    public function replicate(AuthUser $authUser, Kapal $kapal): bool
    {
        return $authUser->can('replicate:kapal');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder:kapal');
    }

}