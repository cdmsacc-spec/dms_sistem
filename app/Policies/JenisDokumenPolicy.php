<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\JenisDokumen;
use Illuminate\Auth\Access\HandlesAuthorization;

class JenisDokumenPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view-any:jenis-dokumen');
    }

    public function view(AuthUser $authUser, JenisDokumen $jenisDokumen): bool
    {
        return $authUser->can('view:jenis-dokumen');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create:jenis-dokumen');
    }

    public function update(AuthUser $authUser, JenisDokumen $jenisDokumen): bool
    {
        return $authUser->can('update:jenis-dokumen');
    }

    public function delete(AuthUser $authUser, JenisDokumen $jenisDokumen): bool
    {
        return $authUser->can('delete:jenis-dokumen');
    }

    public function restore(AuthUser $authUser, JenisDokumen $jenisDokumen): bool
    {
        return $authUser->can('restore:jenis-dokumen');
    }

    public function forceDelete(AuthUser $authUser, JenisDokumen $jenisDokumen): bool
    {
        return $authUser->can('force-delete:jenis-dokumen');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force-delete-any:jenis-dokumen');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore-any:jenis-dokumen');
    }

    public function replicate(AuthUser $authUser, JenisDokumen $jenisDokumen): bool
    {
        return $authUser->can('replicate:jenis-dokumen');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder:jenis-dokumen');
    }

}