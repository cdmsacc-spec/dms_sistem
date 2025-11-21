<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ToReminderCrew;
use Illuminate\Auth\Access\HandlesAuthorization;

class ToReminderCrewPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view-any:to-reminder-crew');
    }

    public function view(AuthUser $authUser, ToReminderCrew $toReminderCrew): bool
    {
        return $authUser->can('view:to-reminder-crew');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create:to-reminder-crew');
    }

    public function update(AuthUser $authUser, ToReminderCrew $toReminderCrew): bool
    {
        return $authUser->can('update:to-reminder-crew');
    }

    public function delete(AuthUser $authUser, ToReminderCrew $toReminderCrew): bool
    {
        return $authUser->can('delete:to-reminder-crew');
    }

    public function restore(AuthUser $authUser, ToReminderCrew $toReminderCrew): bool
    {
        return $authUser->can('restore:to-reminder-crew');
    }

    public function forceDelete(AuthUser $authUser, ToReminderCrew $toReminderCrew): bool
    {
        return $authUser->can('force-delete:to-reminder-crew');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force-delete-any:to-reminder-crew');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore-any:to-reminder-crew');
    }

    public function replicate(AuthUser $authUser, ToReminderCrew $toReminderCrew): bool
    {
        return $authUser->can('replicate:to-reminder-crew');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder:to-reminder-crew');
    }

}