<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;

class CrewDokumen extends Model
{

    use LogsActivity;

    protected $fillable = [
        'id_crew',
        'kategory',
        'jenis_dokumen',
        'nomor_dokumen',
        'tempat_dikeluarkan',
        'tanggal_terbit',
        'tanggal_expired',
        'status',
        'file',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(attributes: [
                'crew.nama_crew',
                'kategory',
                'jenis_dokumen',
                'nomor_dokumen',
                'tempat_dikeluarkan',
                'tanggal_terbit',
                'tanggal_expired',
                'status',
                'file',
            ])
            ->logOnlyDirty()
            ->useLogName('Crew Dokumen')
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "data dokumen crew {$this->crew->nama_crew} {$this->jenis_dokumen} " . "{$eventName}");
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


    protected static function booted()
    {
        static::deleted(function ($model) {
            if ($model->file && \Storage::disk('public')->exists($model->file)) {
                \Storage::disk('public')->delete($model->file);
            }
        });
    }
}
