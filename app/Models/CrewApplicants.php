<?php

namespace App\Models;

use App\Enums\StatusKontrakCrew;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CrewApplicants extends Model
{
    use LogsActivity;
    protected $fillable = [
        'nama_crew', //
        'posisi_dilamar',
        'tempat_lahir', //
        'tanggal_lahir', //
        'jenis_kelamin', //
        'golongan_darah', //
        'status_identitas', //
        'agama', //
        'no_hp', //
        'no_telp_rumah', //
        'email', //
        'kebangsaan', //
        'suku', //
        'alamat_ktp', //
        'alamat_sekarang', //
        'status_rumah', //
        'tinggi_badan',
        'berat_badan',
        'ukuran_waerpack',
        'ukuran_sepatu',
        'nok_nama',
        'nok_hubungan',
        'nok_alamat',
        'nok_hp',
        'foto',
        'status_proses',
    ];
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName('Crew')
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Crew " . $this->nama_crew ?? 'unknown' . " {$eventName}");
    }
    /**
     * Relasi ke tabel crew_certificates
     * Setiap crew bisa punya banyak sertifikat.
     */
    public function crewCertificates()
    {
        return $this->hasMany(CrewCertificates::class, 'applicant_id');
    }

    /**
     * Relasi ke tabel crew_documents
     * Setiap crew bisa punya banyak dokumen.
     */
    public function crewDocument()
    {
        return $this->hasMany(CrewDocuments::class, 'applicant_id');
    }

    /**
     * Relasi ke tabel crew_experiences
     * Setiap crew bisa punya banyak pengalaman kerja.
     */
    public function crewExperience()
    {
        return $this->hasMany(CrewExperiences::class, 'applicant_id');
    }


    /**
     * Relasi ke tabel crew_interviews
     * Setiap crew bisa punya banyak data interview.
     */
    public function crewInterview()
    {
        return $this->hasMany(CrewInterview::class, 'crew_id');
    }

    /**
     * Relasi ke tabel crew_pkl
     * Setiap crew bisa punya banyak catatan PKL / kontrak.
     */
    public function crewPkl()
    {
        return $this->hasMany(CrewPkl::class, 'crew_id');
    }

    public function lastCrewPkl()
    {
        return $this->hasOne(CrewPkl::class, 'crew_id')
            ->where('status_kontrak', StatusKontrakCrew::Active)
            ->latestOfMany(); 
    }

    /**
     * Relasi ke tabel crew_sign_off
     * Setiap crew bisa punya banyak catatan sign-off (akhir kontrak).
     */
    public function crewSignOff()
    {
        return $this->hasMany(CrewSignOff::class, 'crew_id');
    }

    /**
     * Event hooks untuk model Crew.
     * - Saat update: jika foto berubah, hapus file lama dari storage.
     * - Saat delete: hapus file foto dari storage.
     * - Saat delete: hapus juga relasi terkait (cascade manual).
     */

    protected static function booted()
    {
        // Hapus foto lama jika diganti
        static::updating(function ($model) {
            if ($model->isDirty('foto')) {
                $oldFile = $model->getOriginal('foto');

                if ($oldFile && \Storage::disk('public')->exists($oldFile)) {
                    \Storage::disk('public')->delete($oldFile);
                }
            }
        });

        // Hapus foto jika crew dihapus
        static::deleted(function ($model) {
            if ($model->foto && \Storage::disk('public')->exists($model->foto)) {
                \Storage::disk('public')->delete($model->foto);
            }
        });

        // Cascade delete ke relasi
        static::deleting(function ($model) {
            foreach ($model->crewCertificates as $data) {
                $data->delete();
            }
            foreach ($model->crewSignOff as $data) {
                $data->delete();
            }
            foreach ($model->crewPkl as $data) {
                $data->delete();
            }
            foreach ($model->crewInterview as $data) {
                $data->delete();
            }
            foreach ($model->crewDocument as $data) {
                $data->delete();
            }
        });
    }
}
