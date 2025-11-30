<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CrewAppraisal extends Model
{

    use LogsActivity;
    protected $fillable = [
        'id_kontrak',
        'nilai',
        'aprraiser',
        'file',
        'keterangan',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'kontrak.nomor_dokumen',
                'nilai',
                'aprraiser',
                'keterangan',
                'file'
            ])
            ->logOnlyDirty()
            ->useLogName('Appraisal')
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "data appraisal dengan nomor dokumen kontrak {$this->kontrak->nomor_dokumen} " ?? 'unknown' . " {$eventName}");
    }

    public function kontrak()
    {
        return $this->belongsTo(CrewKontrak::class, 'id_kontrak');
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
