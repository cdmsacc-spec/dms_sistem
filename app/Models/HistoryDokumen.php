<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class HistoryDokumen extends Model
{

    use LogsActivity;
    protected $fillable = [
        'id_dokumen',
        'nomor_dokumen',
        'tanggal_terbit',
        'tanggal_expired',
        'file',
    ];

    public function dokumen()
    {
        return $this->belongsTo(Dokumen::class, 'id_dokumen');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(attributes: [
                'dokumen.penerbit',
                'nomor_dokumen',
                'tanggal_terbit',
                'tanggal_expired',
                'file',
            ])
            ->logOnlyDirty()
            ->useLogName('Dokumen')
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Dokumen History Log ");
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        if ($this->id_dokumen) {
            $activity->subject_id = $this->id_dokumen;
            $activity->subject_type = Dokumen::class;
        }
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
