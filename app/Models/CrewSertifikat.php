<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class CrewSertifikat extends Model
{
    use LogsActivity;

    protected $fillable = [
        'id_crew',
        'kategory',
        'nama_sertifikat',
        'nomor_sertifikat',
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
                'nama_sertifikat',
                'nomor_sertifikat',
                'tempat_dikeluarkan',
                'tanggal_terbit',
                'tanggal_expired',
                'status',
                'file',
            ])
            ->logOnlyDirty()
            ->useLogName('Crew Sertifikat')
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "data sertifikat crew {$this->crew->nama_crew}, {$this->nama_sertifikat} " . "{$eventName}");
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
