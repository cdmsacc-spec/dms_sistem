<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ReminderCrew;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReminderCrewPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view-any:reminder-crew');
    }

    public function view(AuthUser $authUser, ReminderCrew $reminderCrew): bool
    {
        return $authUser->can('view:reminder-crew');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create:reminder-crew');
    }

    public function update(AuthUser $authUser, ReminderCrew $reminderCrew): bool
    {
        return $authUser->can('update:reminder-crew');
    }

    public function delete(AuthUser $authUser, ReminderCrew $reminderCrew): bool
    {
        return $authUser->can('delete:reminder-crew');
    }

    public function restore(AuthUser $authUser, ReminderCrew $reminderCrew): bool
    {
        return $authUser->can('restore:reminder-crew');
    }

    public function forceDelete(AuthUser $authUser, ReminderCrew $reminderCrew): bool
    {
        return $authUser->can('force-delete:reminder-crew');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force-delete-any:reminder-crew');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore-any:reminder-crew');
    }

    public function replicate(AuthUser $authUser, ReminderCrew $reminderCrew): bool
    {
        return $authUser->can('replicate:reminder-crew');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder:reminder-crew');
    }

}