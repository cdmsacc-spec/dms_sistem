<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Perusahaan;
use Illuminate\Auth\Access\HandlesAuthorization;

class PerusahaanPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view-any:perusahaan');
    }

    public function view(AuthUser $authUser, Perusahaan $perusahaan): bool
    {
        return $authUser->can('view:perusahaan');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create:perusahaan');
    }

    public function update(AuthUser $authUser, Perusahaan $perusahaan): bool
    {
        return $authUser->can('update:perusahaan');
    }

    public function delete(AuthUser $authUser, Perusahaan $perusahaan): bool
    {
        return $authUser->can('delete:perusahaan');
    }

    public function restore(AuthUser $authUser, Perusahaan $perusahaan): bool
    {
        return $authUser->can('restore:perusahaan');
    }

    public function forceDelete(AuthUser $authUser, Perusahaan $perusahaan): bool
    {
        return $authUser->can('force-delete:perusahaan');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force-delete-any:perusahaan');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore-any:perusahaan');
    }

    public function replicate(AuthUser $authUser, Perusahaan $perusahaan): bool
    {
        return $authUser->can('replicate:perusahaan');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder:perusahaan');
    }

}