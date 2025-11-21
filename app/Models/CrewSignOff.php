<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CrewSignOff extends Model
{
    use LogsActivity;
    protected $fillable = [
        'id_crew',
        'nomor_dokumen',
        'id_alasan',
        'tanggal',
        'keterangan',
        'file',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'crew.nama_crew',
                'nomor_dokumen',
                'id_alasan',
                'tanggal',
                'keterangan',
                'file',
            ])
            ->logOnlyDirty()
            ->useLogName('Sign Off')
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "data sign off crew {$this->crew->nama_crew} dengan nomor dokumen {$this->nomor_dokumen} " ?? 'unknown' . " {$eventName}");
    }

    public function crew()
    {
        return $this->belongsTo(Crew::class, 'id_crew');
    }
    public function alasanBerhenti()
    {
        return $this->belongsTo(AlasanBerhenti::class, 'id_alasan');
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
