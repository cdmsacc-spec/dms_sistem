<?php

namespace App\Models;

use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Jabatan extends Model
{
    use LogsActivity;
    protected $fillable = [
        'nama_jabatan',
        'kode_jabatan',
        'golongan',
        'devisi',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'nama_jabatan',
                'kode_jabatan',
                'golongan',
                'devisi',
            ])
            ->logOnlyDirty()
            ->useLogName('Jabatan')
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "data jabatan {$this->nama_jabatan} " ?? 'unknown' . " {$eventName}");
    }


    public function crew()
    {
        return $this->hasMany(CrewKontrak::class, 'id_jabatan');
    }

    protected static function booted()
    {
        static::deleting(function ($model) {
            if ($model->crew()->exists()) {
                Notification::make()
                    ->title('Gagal menghapus')
                    ->body('Tidak bisa dihapus karena masih memiliki relasi kontrak crew.')
                    ->danger()
                    ->send();
                throw new Halt();
            }
        });
    }
}
