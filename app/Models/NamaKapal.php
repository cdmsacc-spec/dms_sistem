<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class NamaKapal extends Model
{

    use LogsActivity;
    protected $fillable = [
        'perusahaan_id',
        'jenis_kapal_id',
        'nama_kapal',
        'status_certified',
        'tahun_kapal',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'perusahaan.nama_perusahaan',
                'jenisKapal.nama_jenis',
                'nama_kapal',
                'status_certified',
                'tahun_kapal',
            ])
            ->logOnlyDirty()
            ->useLogName('Kapal')
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Kapal " . $this->nama_kapal ?? 'unknown' . " {$eventName}");
    }
    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class, 'perusahaan_id');
    }
    public function jenisKapal()
    {
        return $this->belongsTo(JenisKapal::class, 'jenis_kapal_id');
    }
    public function document()
    {
        return $this->hasMany(Document::class, 'kapal_id');
    }
}
