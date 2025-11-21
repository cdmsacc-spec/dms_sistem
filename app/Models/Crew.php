<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Crew extends Model
{
    use LogsActivity;

    protected $fillable = [
        'nama_crew',
        'posisi_dilamar',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'golongan_darah',
        'status_identitas',
        'agama',
        'no_hp',
        'no_hp_rumah',
        'email',
        'kebangsaan',
        'suku',
        'alamat_ktp',
        'alamat_sekarang',
        'status_rumah',
        'tinggi_badan',
        'berat_badan',
        'ukuran_waerpack',
        'ukuran_sepatu',
        'avatar',
        'status',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'nama_crew',
                'posisi_dilamar',
                'tempat_lahir',
                'tanggal_lahir',
                'jenis_kelamin',
                'golongan_darah',
                'status_identitas',
                'agama',
                'no_hp',
                'no_hp_rumah',
                'email',
                'kebangsaan',
                'suku',
                'alamat_ktp',
                'alamat_sekarang',
                'status_rumah',
                'tinggi_badan',
                'berat_badan',
                'ukuran_waerpack',
                'ukuran_sepatu',
                'avatar',
            ])
            ->logOnlyDirty()
            ->useLogName('Crew')
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "crew " . $this->nama_crew ?? 'unknown' . " {$eventName}");
    }


    public function dokumen()
    {
        return $this->hasMany(CrewDokumen::class, 'id_crew');
    }

    public function sertifikat()
    {
        return $this->hasMany(CrewSertifikat::class, 'id_crew');
    }

    public function experience()
    {
        return $this->hasMany(CrewExperience::class, 'id_crew');
    }

    public function nok()
    {
        return $this->hasMany(CrewNok::class, 'id_crew');
    }

    public function interview()
    {
        return $this->hasMany(CrewInterview::class, 'id_crew')->orderByDesc('created_at');
    }

    public function kontrak()
    {
        return $this->hasMany(CrewKontrak::class, 'id_crew')->orderByDesc('created_at');
    }

    public function lastKontrak()
    {
        return $this->hasOne(CrewKontrak::class, 'id_crew')
            ->latestOfMany();
    }

    public function signon()
    {
        return $this->hasMany(CrewKontrak::class, 'id_crew')->where('kategory', 'signon')->orderByDesc('created_at');
    }

    public function mutasi()
    {
        return $this->hasMany(CrewKontrak::class, 'id_crew')->where('kategory', 'promosi')->orderByDesc('created_at');
    }

    public function signoff()
    {
        return $this->hasMany(CrewSignOff::class, 'id_crew');
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            foreach ($model->sertifikat as $data) {
                $data->create();
            }
            foreach ($model->signoff as $data) {
                $data->create();
            }
            foreach ($model->kontrak as $data) {
                $data->create();
            }
            foreach ($model->interview as $data) {
                $data->create();
            }
            foreach ($model->dokumen as $data) {
                $data->create();
            }
            foreach ($model->nok as $data) {
                $data->create();
            }
            foreach ($model->experience as $data) {
                $data->create();
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('avatar')) {
                $oldFile = $model->getOriginal('avatar');

                if ($oldFile && \Storage::disk('public')->exists($oldFile)) {
                    \Storage::disk('public')->delete($oldFile);
                }
            }
            foreach ($model->sertifikat as $data) {
                $data->update();
            }
            foreach ($model->signoff as $data) {
                $data->update();
            }
            foreach ($model->kontrak as $data) {
                $data->update();
            }
            foreach ($model->interview as $data) {
                $data->update();
            }
            foreach ($model->dokumen as $data) {
                $data->update();
            }
            foreach ($model->nok as $data) {
                $data->update();
            }
            foreach ($model->experience as $data) {
                $data->update();
            }
        });

        static::deleted(function ($model) {
            if ($model->avatar && \Storage::disk('public')->exists($model->avatar)) {
                \Storage::disk('public')->delete($model->avatar);
            }
        });

        // Cascade delete ke relasi
        static::deleting(function ($model) {
            foreach ($model->sertifikat as $data) {
                $data->delete();
            }
            foreach ($model->signoff as $data) {
                $data->delete();
            }
            foreach ($model->kontrak as $data) {
                $data->delete();
            }
            foreach ($model->interview as $data) {
                $data->delete();
            }
            foreach ($model->dokumen as $data) {
                $data->delete();
            }
            foreach ($model->nok as $data) {
                $data->delete();
            }
            foreach ($model->experience as $data) {
                $data->delete();
            }
        });
    }
}
