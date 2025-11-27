<?php

namespace App\Models;

use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class JenisKapal extends Model
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
            ->useLogName('Jenis Kapal')
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Jenis kapal " . $this->nama_jenis ?? 'unknown' . " {$eventName}");
    }

    public function kapal()
    {
        return $this->hasMany(Kapal::class, 'id_jenis_kapal');
    }

    protected static function booted()
    {
        static::deleting(function ($model) {
            if ($model->kapal()->exists()) {
                Notification::make()
                    ->title('Gagal menghapus')
                    ->body('Tidak bisa dihapus karena masih memiliki relasi Kapal.')
                    ->danger()
                    ->send();
                throw new Halt();
            }
        });
    }
}
