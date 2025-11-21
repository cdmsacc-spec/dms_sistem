<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class WilayahOperasional extends Model
{

    use LogsActivity;

    protected $fillable = [
        'nama_wilayah',
        'kode_wilayah',
        'deskripsi',
        
        'ttd_dibuat',
        'ttd_diperiksa',
        'ttd_diketahui_1',
        'ttd_diketahui_2',
        'ttd_disetujui_1',
        'ttd_disetujui_2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName('Wilayah Operasional')
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "wilayah operasional " . $this->nama_wilayah ?? 'unknown' . " {$eventName}");
    }

    public function kapal()
    {
        return $this->hasMany(Kapal::class, 'id_wilayah');
    }
}
