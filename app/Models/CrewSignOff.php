<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CrewSignOff extends Model
{

    use LogsActivity;
    protected $fillable = [
        'crew_id',
        'tanggal',
        'keterangan',
        'file_path',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'crew.nama_crew',
                'tanggal',
                'keterangan',
                'file_path',
            ])
            ->logOnlyDirty()
            ->useLogName('Crew Sign Off')
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Sign Off Crew " . $this->crew->nama_crew ?? 'unknown' . " {$eventName}");
    }

    public function crew()
    {
        return $this->belongsTo(CrewApplicants::class, 'crew_id');
    }
    protected static function booted()
    {

        static::saved(function ($model) {
            if ($model->status_kontrak === 'Active') {
                // Ubah semua kontrak lain milik crew ini jadi expired
                self::where('crew_id', $model->crew_id)
                    ->where('id', '!=', $model->id)
                    ->where('status_kontrak', 'Active')
                    ->update([
                        'status_kontrak' => 'Expired',
                    ]);
            }
        });
        static::created(function ($model) {
            // 1. Ubah semua kontrak PKL jadi expired
            \App\Models\CrewPkl::where('crew_id', $model->crew_id)
                ->where('status_kontrak', ['Active', 'Waiting Approval'])
                ->update([
                    'status_kontrak' => 'Expired',
                ]);

            // 2. Ubah status crew jadi Inactive
            $crew = \App\Models\CrewApplicants::find($model->crew_id);
            if ($crew) {
                $crew->update([
                    'status_proses' => 'Inactive',
                ]);
            }
        });

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
