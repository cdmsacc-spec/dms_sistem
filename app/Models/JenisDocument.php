<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class JenisDocument extends Model
{

     use LogsActivity;
    protected $fillable = [
        'nama_dokumen',
        'deskripsi',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName('Jenis Document')
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Jenis Documents " . $this->nama_dokumen ?? 'unknown' . " {$eventName}");
    }
    public function document()
    {
        return $this->hasMany(Document::class, 'jenis_dokumen_id');
    }
}
