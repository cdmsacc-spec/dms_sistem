<?php

namespace App\Models;

use App\Enums\StatusKontrakCrew;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CrewPkl extends Model
{
    use LogsActivity;
    protected $fillable = [
        'kategory',
        'crew_id',
        'nomor_document',
        'perusahaan_id',
        'jabatan_id',
        'wilayah_id',
        'kapal_id',
        'gaji',
        'start_date',
        'end_date',
        'kontrak_lanjutan',
        'status_kontrak',
        'file_path',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'kategory',
                'crew.nama_crew',
                'nomor_document',
                'perusahaan.nama_perusahaan',
                'jabatan.nama_jabatan',
                'wilayah.nama_wilayah',
                'kapal.nama_kapal',
                'gaji',
                'start_date',
                'end_date',
                'kontrak_lanjutan',
                'status_kontrak',
                'file_path',
                'isNearExpiry'
            ])
            ->logOnlyDirty()
            ->useLogName('Crew PKL')
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Pkl Crew " . $this->crew->nama_crew ?? 'unknown' . " {$eventName}");
    }


    public function crew()
    {
        return $this->belongsTo(CrewApplicants::class, 'crew_id');
    }

    public function kapal()
    {
        return $this->belongsTo(NamaKapal::class, 'kapal_id');
    }

    public function wilayah()
    {
        return $this->belongsTo(WilayahOperasional::class, 'wilayah_id');
    }

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id');
    }

    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class, 'perusahaan_id');
    }

    public function appraisal()
    {
        return $this->hasMany(CrewAppraisal::class, 'pkl_id');
    }

    protected static function booted()
    {
        static::saved(function ($model) {
            if ($model->status_kontrak === StatusKontrakCrew::Active) {
                // Ubah semua kontrak lain milik crew ini jadi expired
              
                self::where('crew_id', $model->crew_id)
                    ->where('id', '!=', $model->id)
                    ->where('status_kontrak', StatusKontrakCrew::Active)
                    ->update([
                        'status_kontrak' =>StatusKontrakCrew::Expired,
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
