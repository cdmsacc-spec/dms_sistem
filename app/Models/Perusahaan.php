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
        'telp',
        'npwp',
        'keterangan',
        'file',
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

    public function kapal()
    {
        return $this->hasMany(Kapal::class, 'id_perusahaan');
    }

    protected static function booted()
    {

        static::updating(function ($model) {
            if ($model->isDirty('file')) {
                $oldFile = $model->getOriginal('file');

                if ($oldFile && \Storage::disk('public')->exists($oldFile)) {
                    \Storage::disk('public')->delete($oldFile);
                }
            }
        });

        static::deleted(function ($model) {
            if ($model->file && \Storage::disk('public')->exists($model->file)) {
                \Storage::disk('public')->delete($model->file);
            }
        });
    }
}
