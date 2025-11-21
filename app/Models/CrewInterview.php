<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CrewInterview extends Model
{
    use LogsActivity;
    protected $fillable = [
        'id_crew',
        'crewing',
        'user_operation',
        'summary',
        'keterangan',
        'tanggal',
        'file',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'crew.nama_crew',
                'crewing',
                'user_operation',
                'summary',
                'keterangan',
                'tanggal',
                'file',
            ])
            ->logOnlyDirty()
            ->useLogName('Interview')
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "data interview crew dengan nama {$this->crew->nama_crew} " ?? 'unknown' . " {$eventName}");
    }

    public function crew()
    {
        return $this->belongsTo(Crew::class, 'id_crew');
    }

    protected static function booted()
    {
        static::updating(function ($model) {
            if ($model->isDirty('file')) {
                $oldFile = $model->getOriginal('file');

                if ($oldFile && \Storage::disk('public')->exists($oldFile)) {
                    \Storage::disk('public')->delete($oldFile);
                }
            }
        });

        static::deleted(function ($model) {
            if ($model->file && \Storage::disk('public')->exists($model->file)) {
                \Storage::disk('public')->delete($model->file);
            }
        });
    }
}
