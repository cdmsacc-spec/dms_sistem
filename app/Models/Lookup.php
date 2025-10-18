<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Lookup extends Model
{

     use LogsActivity;
    protected $fillable = [
        'kategori',
        'code',
        'value',
    ];

     public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName('Lookup')
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Lookup " . $this->kategori ?? 'unknown' . " {$eventName}");
    }
}
