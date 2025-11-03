<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Kirschbaum\Commentions\Comment;
use Kirschbaum\Commentions\Contracts\Commentable;
use Kirschbaum\Commentions\HasComments;
use Parallax\FilamentComments\Models\FilamentComment;
use Parallax\FilamentComments\Models\Traits\HasFilamentComments;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Document extends Model implements Commentable
{
    use HasFilamentComments, LogsActivity, HasComments;
    protected $fillable = [
        'kapal_id',
        'jenis_dokumen_id',
        'created_by',
        'keterangan',
        'penerbit',
        'tempat_penerbitan',
        'is_expiration_check',
        'status',
    ];

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
            ->logOnly([
                'kapal.nama_kapal',
                'jenisDocument.nama_dokumen',
                'createdBy.name',
                'nomor_dokumen',
                'keterangan',
                'penerbit',
                'tempat_penerbitan',
                'is_expiration_check',
                'status',
            ])
            ->logOnlyDirty()
            ->useLogName('Documents')
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Documents Perusahaan" . $this->kapal->perusahaan->nama_perusahaan ?? 'unknown ' . " Kapal " . $this->kapal->nama_kapal ?? 'unknown' . " {$eventName}");
    }
    public function kapal()
    {
        return $this->belongsTo(NamaKapal::class, 'kapal_id');
    }
    public function jenisDocument()
    {
        return $this->belongsTo(JenisDocument::class, 'jenis_dokumen_id');
    }
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function reminders()
    {
        return $this->hasMany(DocumentReminder::class, 'document_id');
    }
    public function expirations()
    {
        return $this->hasMany(DocumentExpiration::class);
    }
    public function latestExpiration()
    {
        return $this->hasOne(DocumentExpiration::class)->latestOfMany();
    }

    public function reminderemail()
    {
        return $this->hasMany(EmailReminder::class, 'document_id');
    }

    protected static function booted()
    {
        static::deleting(function ($document) {
            foreach ($document->expirations as $expiration) {
                $expiration->delete(); // ini akan memicu event deleted di DocumentExpiration
            }
            foreach ($document->reminderemail as $reminderemails) {
                $reminderemails->delete(); // ini akan memicu event deleted di DocumentExpiration
            }
        });
    }
}
