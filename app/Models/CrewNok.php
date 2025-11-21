<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class CrewNok extends Model
{

    use LogsActivity;
    protected $fillable = [
        'id_crew',
        'nama',
        'hubungan',
        'alamat',
        'no_hp',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(attributes: [
                'crew.nama_crew',
                'nama',
                'hubungan',
                'alamat',
                'no_hp',
            ])
            ->logOnlyDirty()
            ->useLogName('Crew Nok')
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "data nok crew {$this->crew->nama_crew} " . "{$eventName}");
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        if ($this->id_crew) {
            $activity->subject_id = $this->id_crew;
            $activity->subject_type = Crew::class;
        }
    }

    public function crew()
    {
        return $this->belongsTo(Crew::class, 'id_crew');
    }
}
