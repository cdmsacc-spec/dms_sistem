<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Dokumen;
use Illuminate\Auth\Access\HandlesAuthorization;

class DokumenPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view-any:dokumen');
    }

    public function view(AuthUser $authUser, Dokumen $dokumen): bool
    {
        return $authUser->can('view:dokumen');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create:dokumen');
    }

    public function update(AuthUser $authUser, Dokumen $dokumen): bool
    {
        return $authUser->can('update:dokumen');
    }

    public function delete(AuthUser $authUser, Dokumen $dokumen): bool
    {
        return $authUser->can('delete:dokumen');
    }

    public function restore(AuthUser $authUser, Dokumen $dokumen): bool
    {
        return $authUser->can('restore:dokumen');
    }

    public function forceDelete(AuthUser $authUser, Dokumen $dokumen): bool
    {
        return $authUser->can('force-delete:dokumen');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force-delete-any:dokumen');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore-any:dokumen');
    }

    public function replicate(AuthUser $authUser, Dokumen $dokumen): bool
    {
        return $authUser->can('replicate:dokumen');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder:dokumen');
    }

}