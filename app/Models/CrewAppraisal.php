<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CrewAppraisal extends Model
{
    use LogsActivity;
    protected $fillable = [
        'pkl_id',
        'appraiser',
        'nilai',
        'keterangan',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'crewPkl.kategory',
                'appraiser',
                'nilai',
                'keterangan',
            ])
            ->logOnlyDirty()
            ->useLogName('Appraisal')
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Appraisal " . $this->crewPkl?->name ?? 'unknown' . " {$eventName}");
    }

    public function crewPkl()
    {
        return $this->belongsTo(CrewPkl::class, 'pkl_id');
    }
}
//