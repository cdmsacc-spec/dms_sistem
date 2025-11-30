<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CrewKontrak extends Model
{
    use LogsActivity;

    protected $fillable = [
        'id_crew',
        'id_perusahaan',
        'id_jabatan',
        'id_kapal',
        'id_wilayah',
        'nomor_dokumen',
        'gaji',
        'berangkat_dari',
        'start_date',
        'end_date',
        'kategory',
        'near_expiry',
        'kontrak_lanjutan',
        'status_kontrak',
        'file',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'crew.nama_crew',
                'perusahaan.nama_perusahaan',
                'jabatan.nama_jabatan',
                'kapal.nama_kapal',
                'wilayah.nama_wilayah',
                'nomor_dokumen',
                'gaji',
                'berangkat_dari',
                'start_date',
                'end_date',
                'kategory',
                'kontrak_lanjutan',
                'status_kontrak',
                'file',
            ])
            ->logOnlyDirty()
            ->useLogName('Kontrak')
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "data kontrak crew {$this->crew->nama_crew} nomor dokumen kontrak {$this->nomor_dokumen} " ?? 'unknown' . " {$eventName}");
    }

    public function crew()
    {
        return $this->belongsTo(Crew::class, 'id_crew');
    }

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'id_jabatan');
    }

    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class, 'id_perusahaan');
    }

    public function kapal()
    {
        return $this->belongsTo(Kapal::class, 'id_kapal');
    }

    public function wilayah()
    {
        return $this->belongsTo(WilayahOperasional::class, 'id_wilayah');
    }

    public function appraisal()
    {
        return $this->hasMany(CrewAppraisal::class, 'id_kontrak')->orderByDesc('created_at');
    }

    protected static function booted()
    {
        static::saved(function ($model) {
            if ($model->status_kontrak === 'active') {
                // Ubah semua kontrak lain milik crew ini jadi expired

                self::where('id_crew', $model->id_crew)
                    ->where('id', '!=', $model->id)
                    ->where('status_kontrak', 'active')
                    ->update([
                        'status_kontrak' => 'expired',
                    ]);
            }
        });
        static::updating(function ($model) {
            if ($model->isDirty('file_path')) {
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

        static::deleting(function ($model) {
            foreach ($model->appraisal as $data) {
                $data->delete();
            }
        });
    }
}
