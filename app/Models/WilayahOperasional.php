<?php

namespace App\Models;

use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
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

        'ttd_dibuat',
        'ttd_diperiksa',
        'ttd_diketahui_1',
        'ttd_diketahui_2',
        'ttd_disetujui_1',
        'ttd_disetujui_2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName('Wilayah Operasional')
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "wilayah operasional " . $this->nama_wilayah ?? 'unknown' . " {$eventName}");
    }

    public function kapal()
    {
        return $this->hasMany(Kapal::class, 'id_wilayah');
    }

    public function kontrak()
    {
        return $this->hasMany(CrewKontrak::class, 'id_wilayah')->orderByDesc('created_at');
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
            if ($model->kontrak()->exists()) {
                Notification::make()
                    ->title('Gagal menghapus')
                    ->body('Data tidak bisa dihapus karena memiliki relasi kontrak crew.')
                    ->danger()
                    ->send();
                throw new Halt();
            }
        });
    }
}
