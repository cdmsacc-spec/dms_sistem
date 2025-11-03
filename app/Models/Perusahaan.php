<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Perusahaan extends Model
{
    use LogsActivity;
    protected $fillable = [
        'nama_perusahaan',
        'kode_perusahaan',
        'alamat',
        'email',
        'telepon',
        'npwp',
        'file_path',
        'keterangan',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName('Perusahaan')
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Perusahaan " . $this->nama_perusahaan ?? 'unknown' . " {$eventName}");
    }
    public function namaKapal()
    {
        return $this->hasMany(NamaKapal::class, 'perusahaan_id');
    }
}
