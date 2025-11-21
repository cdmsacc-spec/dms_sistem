<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Kirschbaum\Commentions\Contracts\Commentable;
use Kirschbaum\Commentions\HasComments;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Kirschbaum\Commentions\Comment;


class Dokumen extends Model implements Commentable
{
    use LogsActivity, HasComments;
    protected $fillable = [
        'id_jenis_dokumen',
        'id_kapal',
        'id_author',
        'keterangan',
        'penerbit',
        'tempat_penerbitan',
        'status',
        'is_expiration',
    ];

    public function kapal()
    {
        return $this->belongsTo(Kapal::class, 'id_kapal');
    }
    public function author()
    {
        return $this->belongsTo(User::class, 'id_author');
    }
    public function jenisDokumen()
    {
        return $this->belongsTo(JenisDokumen::class, 'id_jenis_dokumen');
    }

    //hasmany

    public function historyDokumen()
    {
        return $this->hasMany(HistoryDokumen::class, 'id_dokumen');
    }
    public function reminderDokumen()
    {
        return $this->hasMany(ReminderDokumen::class, 'id_dokumen');
    }
    public function toReminderDokumen()
    {
        return $this->hasMany(ToReminderDokumen::class, 'id_dokumen');
    }

    public function latestHistory()
    {
        return $this->hasOne(HistoryDokumen::class, 'id_dokumen')->latestOfMany();
    }

    public function comment()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
    public function getLastCommentAttribute()
    {
        return $this->comment()->latest('created_at')->first()?->body ?? '-';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(attributes: [
                'jenisDokumen.nama_jenis',
                'kapal.nama_kapal',
                'author.name',
                'keterangan',
                'penerbit',
                'tempat_penerbitan',
                'status',
            ])
            ->logOnlyDirty()
            ->useLogName('Dokumen')
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Dokumen Log ");
    }

    protected static function booted()
    {
        static::deleting(function ($document) {
            foreach ($document->historyDokumen as $historyDokumens) {
                $historyDokumens->delete();
            }
            foreach ($document->toReminderDokumen as $toReminderDokumens) {
                $toReminderDokumens->delete();
            }
        });
    }
}
