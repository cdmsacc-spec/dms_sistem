<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class CrewExperience extends Model
{
    use LogsActivity;

    protected $fillable = [
        'id_crew',
        'nama_kapal',
        'tipe_kapal',
        'nama_perusahaan',
        'posisi',
        'gt_kw',
        'bendera',
        'masa_kerja',
        'periode_awal',
        'periode_akhir',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(attributes: [
                'crew.nama_crew',
                'nama_kapal',
                'tipe_kapal',
                'nama_perusahaan',
                'posisi',
                'gt_kw',
                'bendera',
                'masa_kerja',
                'periode_awal',
                'periode_akhir',
            ])
            ->logOnlyDirty()
            ->useLogName('Crew Experience')
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "data experience crew {$this->crew->nama_crew} " . "{$eventName}");
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
