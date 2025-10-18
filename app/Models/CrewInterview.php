<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CrewInterview extends Model
{
    use LogsActivity;
    protected $fillable = [
        'crew_id',
        'keterangan',
        'hasil_interviewe1',
        'hasil_interviewe2',
        'hasil_interviewe3',
        'sumary',
        'tanggal',
        'file_path',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'crew.nama_crew',
                'keterangan',
                'hasil_interviewe1',
                'hasil_interviewe2',
                'hasil_interviewe3',
                'sumary',
                'tanggal',
                'file_path',
            ])
            ->logOnlyDirty()
            ->useLogName('Crew Interview')
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Interview Crew " . $this->crew->nama_crew ?? 'unknown' . " {$eventName}");
    }

    public function crew()
    {
        return $this->belongsTo(CrewApplicants::class, 'crew_id');
    }

    protected static function booted()
    {
        static::updating(function ($model) {
            if ($model->isDirty('file_path')) {
                $oldFile = $model->getOriginal('file_path');

                if ($oldFile && \Storage::disk('public')->exists($oldFile)) {
                    \Storage::disk('public')->delete($oldFile);
                }
            }
        });

        static::deleted(function ($model) {
            if ($model->file_path && \Storage::disk('public')->exists($model->file_path)) {
                \Storage::disk('public')->delete($model->file_path);
            }
        });
    }
}
