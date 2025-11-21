<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ToReminderDokumen extends Model
{

    use LogsActivity;
    protected $fillable = [
        'id_dokumen',
        'nama',
        'send_to',
        'type',
    ];

    public function dokumen()
    {
        return $this->belongsTo(Dokumen::class, 'id_dokumen');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(attributes: [
                'nama',
                'send_to',
                'type',
            ])
            ->logOnlyDirty()
            ->useLogName('Dokumen')
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Dokumen Reminder Send To Log ");
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        if ($this->id_dokumen) {
            $activity->subject_id = $this->id_dokumen;
            $activity->subject_type = Dokumen::class;
        }
    }
}
