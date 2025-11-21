<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Jabatan;
use Illuminate\Auth\Access\HandlesAuthorization;

class JabatanPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view-any:jabatan');
    }

    public function view(AuthUser $authUser, Jabatan $jabatan): bool
    {
        return $authUser->can('view:jabatan');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create:jabatan');
    }

    public function update(AuthUser $authUser, Jabatan $jabatan): bool
    {
        return $authUser->can('update:jabatan');
    }

    public function delete(AuthUser $authUser, Jabatan $jabatan): bool
    {
        return $authUser->can('delete:jabatan');
    }

    public function restore(AuthUser $authUser, Jabatan $jabatan): bool
    {
        return $authUser->can('restore:jabatan');
    }

    public function forceDelete(AuthUser $authUser, Jabatan $jabatan): bool
    {
        return $authUser->can('force-delete:jabatan');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force-delete-any:jabatan');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore-any:jabatan');
    }

    public function replicate(AuthUser $authUser, Jabatan $jabatan): bool
    {
        return $authUser->can('replicate:jabatan');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder:jabatan');
    }

}