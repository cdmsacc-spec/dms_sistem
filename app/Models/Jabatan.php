<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Jabatan extends Model
{
     use LogsActivity;
    protected $fillable = [
        'nama_jabatan',
        'golongan',
        'devisi',
        'kode_jabatan',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName('Jabatan')
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Jabatan " . $this->nama_jabatan?? 'unknown' . " {$eventName}");
    }
    public function crewPkl()
    {
        return $this->hasMany(CrewPkl::class, 'jabatan_id');
    }
}
