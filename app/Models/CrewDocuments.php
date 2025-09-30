<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CrewDocuments extends Model
{
    use LogsActivity;
    protected $fillable = [
        'applicant_id',
        'kategory',
        'jenis_document',
        'nomor_document',
        'tempat_dikeluarkan',
        'tanggal_dikeluarkan',
        'tanggal_expired',
        'file_path',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'applicant.nama_crew',
                'kategory',
                'jenis_document',
                'nomor_document',
                'tempat_dikeluarkan',
                'tanggal_dikeluarkan',
                'tanggal_expired',
                'file_path',
            ])
            ->logOnlyDirty()
            ->useLogName('Crew Documents')
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Document Crew " . $this->applicant->nama_crew ?? 'unknown' . " {$eventName}");
    }

    public function applicant()
    {
        return $this->belongsTo(CrewApplicants::class, 'applicant_id');
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
//git checkout -b modul_staff_crew
//git commit -m "first commit mdoul 2"
//git push origin modul_staff_crew
