<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class JenisDokumen extends Model
{
    use LogsActivity;
    protected $fillable = [
        'nama_jenis',
        'deskripsi',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName('Jenis Dokumen')
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Jenis Dokumen " . $this->nama_jenis ?? 'unknown' . " {$eventName}");
    }

    public function dokumen()
    {
        return $this->hasMany(Dokumen::class, 'id_jenis_dokumen');
    }
}
