<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\AlasanBerhenti;
use Illuminate\Auth\Access\HandlesAuthorization;

class AlasanBerhentiPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view-any:alasan-berhenti');
    }

    public function view(AuthUser $authUser, AlasanBerhenti $alasanBerhenti): bool
    {
        return $authUser->can('view:alasan-berhenti');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create:alasan-berhenti');
    }

    public function update(AuthUser $authUser, AlasanBerhenti $alasanBerhenti): bool
    {
        return $authUser->can('update:alasan-berhenti');
    }

    public function delete(AuthUser $authUser, AlasanBerhenti $alasanBerhenti): bool
    {
        return $authUser->can('delete:alasan-berhenti');
    }

    public function restore(AuthUser $authUser, AlasanBerhenti $alasanBerhenti): bool
    {
        return $authUser->can('restore:alasan-berhenti');
    }

    public function forceDelete(AuthUser $authUser, AlasanBerhenti $alasanBerhenti): bool
    {
        return $authUser->can('force-delete:alasan-berhenti');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force-delete-any:alasan-berhenti');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore-any:alasan-berhenti');
    }

    public function replicate(AuthUser $authUser, AlasanBerhenti $alasanBerhenti): bool
    {
        return $authUser->can('replicate:alasan-berhenti');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder:alasan-berhenti');
    }

}