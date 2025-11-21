<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ReminderCrew extends Model
{
    use LogsActivity;
    protected $fillable = [
        'reminder_hari',
        'reminder_jam',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(attributes: [
                'reminder_hari',
                'reminder_jam',
            ])
            ->logOnlyDirty()
            ->useLogName('Reminder')
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Reminder " . "{$eventName}");
    }

}
