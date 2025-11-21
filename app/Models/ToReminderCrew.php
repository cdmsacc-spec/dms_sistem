<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ToReminderCrew extends Model
{
    use LogsActivity;

    protected $fillable = [
        'nama',
        'send_to',
        'type',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(attributes: [
                'nama',
                'send_to',
                'type',
            ])
            ->logOnlyDirty()
            ->useLogName('Reminder To')
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "data reminder to " . "{$eventName}");
    }
}
