<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\JenisKapal;
use Illuminate\Auth\Access\HandlesAuthorization;

class JenisKapalPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view-any:jenis-kapal');
    }

    public function view(AuthUser $authUser, JenisKapal $jenisKapal): bool
    {
        return $authUser->can('view:jenis-kapal');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create:jenis-kapal');
    }

    public function update(AuthUser $authUser, JenisKapal $jenisKapal): bool
    {
        return $authUser->can('update:jenis-kapal');
    }

    public function delete(AuthUser $authUser, JenisKapal $jenisKapal): bool
    {
        return $authUser->can('delete:jenis-kapal');
    }

    public function restore(AuthUser $authUser, JenisKapal $jenisKapal): bool
    {
        return $authUser->can('restore:jenis-kapal');
    }

    public function forceDelete(AuthUser $authUser, JenisKapal $jenisKapal): bool
    {
        return $authUser->can('force-delete:jenis-kapal');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force-delete-any:jenis-kapal');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore-any:jenis-kapal');
    }

    public function replicate(AuthUser $authUser, JenisKapal $jenisKapal): bool
    {
        return $authUser->can('replicate:jenis-kapal');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder:jenis-kapal');
    }

}