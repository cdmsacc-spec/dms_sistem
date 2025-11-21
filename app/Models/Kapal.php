<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Kapal extends Model
{
    use LogsActivity;
    protected $fillable = [
        'id_perusahaan',
        'id_jenis_kapal',
        'nama_kapal',
        'id_wilayah',
        'status_certified',
        'tahun_kapal',
        'keterangan',
        'file',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'perusahaan.nama_perusahaan',
                'jenisKapal.nama_jenis',
                'nama_kapal',
                'wilayahOperasional.nama_wilayah',
                'status_certified',
                'tahun_kapal',
                'keterangan',
                'file',
            ])
            ->logOnlyDirty()
            ->useLogName('Kapal')
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Kapal " . $this->nama_kapal ?? 'unknown' . " {$eventName}");
    }

    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class, 'id_perusahaan');
    }
    public function jenisKapal()
    {
        return $this->belongsTo(JenisKapal::class, 'id_jenis_kapal');
    }
    public function wilayahOperasional()
    {
        return $this->belongsTo(WilayahOperasional::class, 'id_wilayah');
    }
    public function dokumen()
    {
        return $this->hasMany(Dokumen::class, 'id_kapal');
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
