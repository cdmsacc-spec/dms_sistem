<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CrewExperiences extends Model
{
    use LogsActivity;
    protected $fillable = [
        'applicant_id',
        'nama_kapal',
        'nama_perusahaan',
        'posisi',
        'gt_kw',
        'tipe_kapal',
        'bendera',
        'periode_awal',
        'periode_akhir',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'applicant.nama_crew',
                'nama_kapal',
                'nama_perusahaan',
                'posisi',
                'gt_kw',
                'tipe_kapal',
                'bendera',
                'periode_awal',
                'periode_akhir',
            ])
            ->logOnlyDirty()
            ->useLogName('Crew Experience')
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Experience Crew " . $this->applicant->nama_crew ?? 'unknown' . " {$eventName}");
    }

    public function applicant()
    {
        return $this->belongsTo(CrewApplicants::class, 'applicant_id');
    }
}
