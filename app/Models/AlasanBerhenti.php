<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class AlasanBerhenti extends Model
{

    use LogsActivity;
    protected $fillable = [
        'nama_alasan',
        'keterangan',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'nama_alasan',
                'keterangan',
            ])
            ->logOnlyDirty()
            ->useLogName('Alasan Berhenti')
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "perubahan data master (alasan berhenti) dengan event" ?? 'unknown' . " {$eventName}");
    }
}
