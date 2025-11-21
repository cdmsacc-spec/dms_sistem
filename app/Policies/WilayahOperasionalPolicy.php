<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\WilayahOperasional;
use Illuminate\Auth\Access\HandlesAuthorization;

class WilayahOperasionalPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view-any:wilayah-operasional');
    }

    public function view(AuthUser $authUser, WilayahOperasional $wilayahOperasional): bool
    {
        return $authUser->can('view:wilayah-operasional');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create:wilayah-operasional');
    }

    public function update(AuthUser $authUser, WilayahOperasional $wilayahOperasional): bool
    {
        return $authUser->can('update:wilayah-operasional');
    }

    public function delete(AuthUser $authUser, WilayahOperasional $wilayahOperasional): bool
    {
        return $authUser->can('delete:wilayah-operasional');
    }

    public function restore(AuthUser $authUser, WilayahOperasional $wilayahOperasional): bool
    {
        return $authUser->can('restore:wilayah-operasional');
    }

    public function forceDelete(AuthUser $authUser, WilayahOperasional $wilayahOperasional): bool
    {
        return $authUser->can('force-delete:wilayah-operasional');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force-delete-any:wilayah-operasional');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore-any:wilayah-operasional');
    }

    public function replicate(AuthUser $authUser, WilayahOperasional $wilayahOperasional): bool
    {
        return $authUser->can('replicate:wilayah-operasional');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder:wilayah-operasional');
    }

}