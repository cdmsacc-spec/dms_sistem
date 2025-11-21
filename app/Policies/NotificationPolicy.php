<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Notification;
use Illuminate\Auth\Access\HandlesAuthorization;

class NotificationPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view-any:notification');
    }

    public function view(AuthUser $authUser, Notification $notification): bool
    {
        return $authUser->can('view:notification');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create:notification');
    }

    public function update(AuthUser $authUser, Notification $notification): bool
    {
        return $authUser->can('update:notification');
    }

    public function delete(AuthUser $authUser, Notification $notification): bool
    {
        return $authUser->can('delete:notification');
    }

    public function restore(AuthUser $authUser, Notification $notification): bool
    {
        return $authUser->can('restore:notification');
    }

    public function forceDelete(AuthUser $authUser, Notification $notification): bool
    {
        return $authUser->can('force-delete:notification');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force-delete-any:notification');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore-any:notification');
    }

    public function replicate(AuthUser $authUser, Notification $notification): bool
    {
        return $authUser->can('replicate:notification');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder:notification');
    }

}