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
    ];
    public function wilayahOperasional()
    {
        return $this->hasMany(NamaKapal::class, 'wilayah_operasional_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName('Wilayah Operasional')
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Wilayah Operasional " . $this->nama_wilayah ?? 'unknown' . " {$eventName}");
    }
}
